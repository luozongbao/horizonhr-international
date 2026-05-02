<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetJob;
use App\Models\SocialAuthentication;
use App\Services\AuthService;
use App\Services\SocialAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class SocialAuthController extends Controller
{
    /**
     * Map public provider names to Socialite driver keys.
     * 'linkedin' → 'linkedin-openid' (OpenID Connect, required since 2023)
     * 'wechat'   → 'weixin'          (SocialiteProviders/WeChat driver)
     */
    private const DRIVER_MAP = [
        'google'   => 'google',
        'facebook' => 'facebook',
        'linkedin' => 'linkedin-openid',
        'wechat'   => 'weixin',
    ];

    public function __construct(
        protected SocialAuthService $socialAuthService,
        protected AuthService       $authService,
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/auth/social/{provider}?role=student|enterprise
    // Redirect to the OAuth provider's login page.
    // ─────────────────────────────────────────────────────────────────────────

    public function redirect(string $provider, Request $request): JsonResponse|RedirectResponse
    {
        $driver = $this->resolveDriver($provider);

        if (! $driver) {
            return $this->invalidProvider();
        }

        $role = in_array($request->query('role'), ['student', 'enterprise'])
            ? $request->query('role')
            : 'student';

        // Encode role in the OAuth state parameter so the callback knows what to create.
        return Socialite::driver($driver)
            ->stateless()
            ->with(['state' => base64_encode(json_encode(['role' => $role]))])
            ->redirect();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/auth/social/{provider}/callback
    // Handle OAuth callback: resolve user, issue Sanctum token, redirect frontend.
    // ─────────────────────────────────────────────────────────────────────────

    public function callback(string $provider, Request $request): RedirectResponse|JsonResponse
    {
        $driver = $this->resolveDriver($provider);

        if (! $driver) {
            return $this->redirectWithError('INVALID_PROVIDER');
        }

        // Decode role from state parameter (default to student if missing/invalid)
        $role = 'student';
        try {
            $state = $request->query('state');
            if ($state) {
                $decoded = json_decode(base64_decode($state), true);
                if (isset($decoded['role']) && in_array($decoded['role'], ['student', 'enterprise'])) {
                    $role = $decoded['role'];
                }
            }
        } catch (Throwable) {
            // Ignore malformed state — default to student
        }

        try {
            $socialUser = Socialite::driver($driver)->stateless()->user();
        } catch (Throwable $e) {
            return $this->redirectWithError('OAUTH_FAILED');
        }

        $isNewUser = ! SocialAuthentication::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->exists();

        $user = $this->socialAuthService->findOrCreateUser($provider, $socialUser, $role);

        // Block admin accounts from social auth
        if ($user->role === 'admin') {
            return $this->redirectWithError('FORBIDDEN');
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        // Dispatch enterprise notification for brand-new enterprise accounts
        if ($isNewUser && $user->role === 'enterprise') {
            // Email job will be implemented in TASK-017 (email templates)
            // dispatch(new SendEnterpriseNotificationJob($user));
        }

        $token       = $user->createToken('auth_token')->plainTextToken;
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

        return redirect()->away(
            $frontendUrl . '/auth/callback#token=' . urlencode($token) . '&role=' . urlencode($user->role)
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // DELETE /api/auth/social/{provider}
    // Unlink a social provider from the authenticated user's account.
    // ─────────────────────────────────────────────────────────────────────────

    public function unlink(string $provider, Request $request): JsonResponse
    {
        $driver = $this->resolveDriver($provider);

        if (! $driver) {
            return $this->invalidProvider();
        }

        $user = $request->user();

        // Block admin accounts
        if ($user->role === 'admin') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'FORBIDDEN', 'message' => 'Admin accounts cannot use social auth.'],
            ], 403);
        }

        // Prevent unlinking if the user has no password set (would lock them out)
        if (empty($user->password)) {
            $socialCount = SocialAuthentication::where('user_id', $user->id)->count();

            if ($socialCount <= 1) {
                return response()->json([
                    'success' => false,
                    'error'   => [
                        'code'    => 'CANNOT_UNLINK',
                        'message' => 'You must set a password before unlinking your only social login.',
                    ],
                ], 422);
            }
        }

        $deleted = SocialAuthentication::where('user_id', $user->id)
            ->where('provider', $provider)
            ->delete();

        if (! $deleted) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NOT_LINKED', 'message' => 'This provider is not linked to your account.'],
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => ucfirst($provider) . ' has been unlinked from your account.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function resolveDriver(string $provider): ?string
    {
        return self::DRIVER_MAP[strtolower($provider)] ?? null;
    }

    private function invalidProvider(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => 'INVALID_PROVIDER',
                'message' => 'Unsupported OAuth provider. Supported: google, facebook, linkedin, wechat.',
            ],
        ], 422);
    }

    private function redirectWithError(string $code): RedirectResponse
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');

        return redirect()->away($frontendUrl . '/auth/callback#error=' . urlencode($code));
    }
}
