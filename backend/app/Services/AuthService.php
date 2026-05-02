<?php

namespace App\Services;

use App\Jobs\SendEmailConfirmationJob;
use App\Jobs\SendEnterprisePendingNotifyAdminJob;
use App\Jobs\SendPasswordResetJob;
use App\Models\ConsentLog;
use App\Models\EmailConfirmation;
use App\Models\Enterprise;
use App\Models\PasswordReset;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Create a student user + student profile row in a single transaction.
     * Also logs PDPA consent and dispatches email confirmation.
     */
    public function createStudent(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'role'           => 'student',
                'email'          => $data['email'],
                'password'       => Hash::make($data['password']),
                'status'         => 'pending',
                'email_verified' => false,
                'prefer_lang'    => $data['prefer_lang'] ?? 'en',
            ]);

            Student::create([
                'user_id'     => $user->id,
                'name'        => $data['name'],
                'nationality' => $data['nationality'] ?? null,
                'phone'       => $data['phone'] ?? null,
            ]);

            $this->logConsent($user, $data);

            $token = $this->generateEmailConfirmationToken($user);
            dispatch(new SendEmailConfirmationJob($user, $token));

            return $user;
        });
    }

    /**
     * Create an enterprise user + enterprise profile row in a single transaction.
     * Also logs PDPA consent and dispatches email confirmation.
     */
    public function createEnterprise(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'role'              => 'enterprise',
                'email'             => $data['email'],
                'password'          => Hash::make($data['password']),
                'status'            => 'pending',
                'enterprise_status' => 'pending',
                'email_verified'    => false,
                'prefer_lang'       => $data['prefer_lang'] ?? 'en',
            ]);

            Enterprise::create([
                'user_id'      => $user->id,
                'company_name' => $data['company_name'],
                'industry'     => $data['industry'] ?? null,
                'contact_name' => $data['name'] ?? null,
                'contact_phone'=> $data['phone'] ?? null,
            ]);

            $this->logConsent($user, $data);

            $token = $this->generateEmailConfirmationToken($user);
            dispatch(new SendEmailConfirmationJob($user, $token));

            // Notify all admins that a new enterprise is pending review.
            // The admin notification fires after email is confirmed (in AuthController::confirmEmail),
            // but we also dispatch here so admins are aware immediately.
            dispatch(new SendEnterprisePendingNotifyAdminJob($user));

            return $user;
        });
    }
     */
    public function generateEmailConfirmationToken(User $user): string
    {
        // Invalidate any existing tokens for this user
        EmailConfirmation::where('user_id', $user->id)
            ->whereNull('confirmed_at')
            ->delete();

        $token = bin2hex(random_bytes(32)); // 64-char hex

        EmailConfirmation::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'token'      => $token,
            'expires_at' => now()->addHours(24),
        ]);

        return $token;
    }

    /**
     * Create a password_resets row and return the plain token.
     */
    public function generatePasswordResetToken(User $user): string
    {
        // Invalidate any existing unused tokens
        PasswordReset::where('user_id', $user->id)
            ->whereNull('used_at')
            ->delete();

        $token = bin2hex(random_bytes(32)); // 64-char hex

        PasswordReset::create([
            'user_id'    => $user->id,
            'email'      => $user->email,
            'token'      => $token,
            'expires_at' => now()->addHours(2),
        ]);

        return $token;
    }

    /**
     * Log PDPA consent at registration.
     */
    private function logConsent(User $user, array $data): void
    {
        ConsentLog::create([
            'user_id'      => $user->id,
            'consent_type' => 'registration_pdpa',
            'consented_at' => now(),
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }

    /**
     * Load the appropriate role profile alongside the user.
     */
    public function userWithProfile(User $user): array
    {
        $profile = match ($user->role) {
            'student'    => $user->student,
            'enterprise' => $user->enterprise,
            'admin'      => $user->admin,
            default      => null,
        };

        return [
            'id'             => $user->id,
            'email'          => $user->email,
            'role'           => $user->role,
            'status'         => $user->status,
            'email_verified' => $user->email_verified,
            'prefer_lang'    => $user->prefer_lang,
            'profile'        => $profile,
        ];
    }
}
