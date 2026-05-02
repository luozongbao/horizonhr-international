<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ConfirmEmailRequest;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Jobs\SendPasswordResetJob;
use App\Models\EmailConfirmation;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(protected AuthService $authService)
    {
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/register
    // ─────────────────────────────────────────────────────────────────────────

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($data['role'] === 'student') {
            $user    = $this->authService->createStudent($data);
            $message = 'Registration successful. Please check your email to verify your account.';
        } else {
            $user    = $this->authService->createEnterprise($data);
            $message = 'Registration received. After email confirmation, your account will be reviewed by our team.';
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'             => $user->id,
                    'email'          => $user->email,
                    'role'           => $user->role,
                    'status'         => $user->status,
                    'email_verified' => $user->email_verified,
                ],
            ],
            'message' => $message,
        ], 201);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/login
    // ─────────────────────────────────────────────────────────────────────────

    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'INVALID_CREDENTIALS',
                    'message' => 'Invalid email or password.',
                ],
            ], 401);
        }

        if ($user->status === 'deleted') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_NOT_FOUND',
                    'message' => 'Account not found.',
                ],
            ], 401);
        }

        if (! $user->email_verified) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'EMAIL_NOT_VERIFIED',
                    'message' => 'Please verify your email address before logging in.',
                ],
            ], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_SUSPENDED',
                    'message' => 'Your account has been suspended. Please contact support.',
                ],
            ], 401);
        }

        // Update last login info
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $request->ip(),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
                'user'       => $this->authService->userWithProfile($user),
            ],
            'message' => 'Login successful.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/logout
    // ─────────────────────────────────────────────────────────────────────────

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Logged out successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/refresh
    // ─────────────────────────────────────────────────────────────────────────

    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'data'    => [
                'token'      => $token,
                'token_type' => 'Bearer',
            ],
            'message' => 'Token refreshed.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // GET /api/auth/me
    // ─────────────────────────────────────────────────────────────────────────

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => [
                'user' => $this->authService->userWithProfile($request->user()),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/forgot-password
    // ─────────────────────────────────────────────────────────────────────────

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        // Always return success to prevent email enumeration
        $user = User::where('email', $request->email)
            ->whereNotIn('status', ['deleted'])
            ->first();

        if ($user) {
            $token = $this->authService->generatePasswordResetToken($user);
            dispatch(new SendPasswordResetJob($user, $token));
        }

        return response()->json([
            'success' => true,
            'data'    => null,
            'message' => 'Password reset instructions have been sent to your email.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/reset-password
    // ─────────────────────────────────────────────────────────────────────────

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $reset = PasswordReset::where('token', $request->token)
            ->where('email', $request->email)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $reset) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'TOKEN_INVALID',
                    'message' => 'This password reset token is invalid or has expired.',
                ],
            ], 422);
        }

        $user = User::find($reset->user_id);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_NOT_FOUND',
                    'message' => 'Account not found.',
                ],
            ], 422);
        }

        $user->update(['password' => Hash::make($request->password)]);
        $reset->update(['used_at' => now()]);

        // Revoke all existing tokens for security
        $user->tokens()->delete();

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => ['id' => $user->id, 'email' => $user->email],
            ],
            'message' => 'Password has been reset successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // POST /api/auth/confirm-email
    // ─────────────────────────────────────────────────────────────────────────

    public function confirmEmail(ConfirmEmailRequest $request): JsonResponse
    {
        $confirmation = EmailConfirmation::where('token', $request->token)
            ->where('email', $request->email)
            ->whereNull('confirmed_at')
            ->where('expires_at', '>', now())
            ->first();

        if (! $confirmation) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'TOKEN_INVALID',
                    'message' => 'This email confirmation token is invalid or has expired.',
                ],
            ], 422);
        }

        $user = User::find($confirmation->user_id);
        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'ACCOUNT_NOT_FOUND', 'message' => 'Account not found.'],
            ], 422);
        }

        $confirmation->update(['confirmed_at' => now()]);
        $user->update(['email_verified' => true]);

        // Students become active immediately; enterprises stay pending (await admin)
        if ($user->role === 'student') {
            $user->update(['status' => 'active']);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'user' => [
                    'id'             => $user->id,
                    'email'          => $user->email,
                    'email_verified' => true,
                    'status'         => $user->fresh()->status,
                ],
            ],
            'message' => $user->role === 'student'
                ? 'Email confirmed successfully. Your account is now active.'
                : 'Email confirmed. Your account is pending admin review.',
        ]);
    }
}
