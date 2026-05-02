<?php

namespace App\Services;

use App\Models\ConsentLog;
use App\Models\Enterprise;
use App\Models\SocialAuthentication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Contracts\User as SocialUser;

class SocialAuthService
{
    /**
     * Find or create a local User from a social provider user.
     *
     * Resolution order:
     * 1. Existing social_authentications row  → return that user
     * 2. Email match on users table           → link provider, return user
     * 3. No match                             → create new user + profile
     *
     * @param  string     $provider    Normalised driver key (google, facebook, linkedin, wechat)
     * @param  SocialUser $socialUser  Socialite user contract
     * @param  string     $role        'student' | 'enterprise'
     * @return User
     */
    public function findOrCreateUser(string $provider, SocialUser $socialUser, string $role): User
    {
        // 1. Look up existing social auth record
        $existing = SocialAuthentication::where('provider', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($existing) {
            // Refresh token in case it changed
            $existing->update([
                'access_token'    => encrypt($socialUser->token ?? ''),
                'provider_avatar' => $socialUser->getAvatar(),
            ]);

            return $existing->user;
        }

        // 2. Email match — link to existing account
        $email = $socialUser->getEmail();
        $user  = $email ? User::where('email', $email)->first() : null;

        if ($user) {
            $this->linkProvider($user, $provider, $socialUser);
            return $user;
        }

        // 3. Create brand-new user
        return $this->createUserFromSocial($provider, $socialUser, $role);
    }

    /**
     * Attach a social provider record to an existing user.
     */
    public function linkProvider(User $user, string $provider, SocialUser $socialUser): void
    {
        SocialAuthentication::updateOrCreate(
            [
                'user_id'  => $user->id,
                'provider' => $provider,
            ],
            [
                'provider_id'     => $socialUser->getId(),
                'provider_email'  => $socialUser->getEmail(),
                'provider_name'   => $socialUser->getName(),
                'provider_avatar' => $socialUser->getAvatar(),
                'access_token'    => encrypt($socialUser->token ?? ''),
            ]
        );
    }

    /**
     * Create a new user + role profile from social data.
     * Students get status=active (email trusted). Enterprises stay pending.
     */
    private function createUserFromSocial(string $provider, SocialUser $socialUser, string $role): User
    {
        return DB::transaction(function () use ($provider, $socialUser, $role) {
            $isStudent = $role === 'student';

            $user = User::create([
                'role'              => $role,
                'email'             => $socialUser->getEmail(),
                'password'          => null, // no password for social-only accounts
                'status'            => $isStudent ? 'active' : 'pending',
                'enterprise_status' => $isStudent ? null : 'pending',
                'email_verified'    => true, // social provider email is trusted
                'prefer_lang'       => 'en',
            ]);

            if ($isStudent) {
                Student::create([
                    'user_id' => $user->id,
                    'name'    => $socialUser->getName() ?? $socialUser->getEmail(),
                ]);
            } else {
                Enterprise::create([
                    'user_id'      => $user->id,
                    'company_name' => $socialUser->getName() ?? 'Company',
                ]);
            }

            $this->linkProvider($user, $provider, $socialUser);
            $this->logConsent($user);

            return $user;
        });
    }

    /**
     * Log PDPA consent for new social-registered users.
     */
    private function logConsent(User $user): void
    {
        ConsentLog::create([
            'user_id'      => $user->id,
            'consent_type' => 'registration_pdpa',
            'consented_at' => now(),
            'ip_address'   => request()->ip(),
            'user_agent'   => request()->userAgent(),
        ]);
    }
}
