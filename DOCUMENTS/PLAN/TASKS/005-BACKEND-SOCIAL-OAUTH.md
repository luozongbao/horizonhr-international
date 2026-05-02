# TASK-005: Backend Social OAuth Module

**Phase:** 2 — Backend: Authentication  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** HIGH  

---

## Objective

Implement social login/register for Google, Facebook, LinkedIn, and WeChat for student and enterprise accounts. Social auth is NOT available for admin accounts. On first social login, auto-create the user account. On subsequent logins, return the existing account token.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 2.6 (Social Authentication: 2.6.1–2.6.3)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 1.2.21 (social_authentications), Section 2.3.2
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.4 (Social Authentication)

---

## API Endpoints to Implement

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/auth/social/{provider}` | None | Redirect to provider OAuth page |
| GET | `/api/auth/social/{provider}/callback` | None | Handle provider callback, return token |
| DELETE | `/api/auth/social/{provider}` | Bearer | Unlink social provider from account |

`{provider}` values: `google`, `facebook`, `linkedin`, `wechat`

---

## Packages to Install

```bash
composer require laravel/socialite
composer require socialiteproviders/wechat
```

Add WeChat provider to `config/services.php` and `EventServiceProvider`.

---

## Deliverables

### Controllers
- `app/Http/Controllers/Auth/SocialAuthController.php`
  - `redirect(string $provider)` — validate provider, return OAuth redirect URL
  - `callback(string $provider)` — handle OAuth callback, create/find user, return token
  - `unlink(string $provider, Request $request)` — remove social_authentications row

### Services
- `app/Services/SocialAuthService.php`
  - `findOrCreateUser(string $provider, \Laravel\Socialite\Contracts\User $socialUser, string $role): User`
    - Look up `social_authentications` by provider + provider_id
    - If found: return existing user
    - If not found by provider_id: check if email matches an existing user → link account
    - If no match: create new user (role = student by default, or enterprise if specified) + create social_auth row
  - `linkProvider(User $user, string $provider, \Laravel\Socialite\Contracts\User $socialUser): void`

### Middleware / Validation
- Validate `$provider` is one of: `google`, `facebook`, `linkedin`, `wechat`
- Return `INVALID_PROVIDER` error for unknown providers
- Block admin accounts from using social auth — return 403

### Routes
```php
Route::prefix('auth/social')->group(function () {
    Route::get('{provider}', [SocialAuthController::class, 'redirect']);
    Route::get('{provider}/callback', [SocialAuthController::class, 'callback']);
    Route::delete('{provider}', [SocialAuthController::class, 'unlink'])
        ->middleware('auth:sanctum');
});
```

---

## OAuth Callback Flow

1. Receive callback from provider with auth code
2. Exchange code for user info via Socialite
3. Call `SocialAuthService::findOrCreateUser()`
4. If new enterprise account: send enterprise pending admin notification email (job)
5. If new student account: mark email as verified (social email is trusted), set status = active
6. Record consent log entry for new users
7. Issue Sanctum token
8. Redirect to frontend with token in URL fragment: `{FRONTEND_URL}/auth/callback#token={token}&role={role}`
   - Frontend extracts token from URL and stores in localStorage

---

## Social Auth Config (`config/services.php`)

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('APP_URL') . '/api/auth/social/google/callback',
],
'facebook' => [ ... ],
'linkedin-openid' => [ ... ],  // Use LinkedIn OpenID provider
'wechat' => [ ... ],            // Use SocialiteProviders/WeChat
```

---

## social_authentications Table Usage

When creating a social auth record, store:
- `provider_id` — the ID from the social platform
- `provider_email` — email from social platform (may differ from account email)
- `provider_name` — display name from social platform
- `provider_avatar` — avatar URL from social platform
- `access_token` — encrypted using `encrypt()` helper

---

## Acceptance Criteria

- [ ] `GET /api/auth/social/google` returns a redirect URL (302)
- [ ] `GET /api/auth/social/google/callback` with valid code returns Bearer token
- [ ] New user created in `users` + role profile table on first social login
- [ ] Existing user found and token returned on subsequent social login
- [ ] Email from social provider used to link existing email/password account
- [ ] Social auth blocked for admin accounts (returns 403)
- [ ] `DELETE /api/auth/social/google` removes social link (must have password set first)
- [ ] Invalid provider name returns `INVALID_PROVIDER` error
- [ ] `social_authentications` row created with encrypted access_token
- [ ] New enterprise via social → notification email dispatched
- [ ] New student via social → status set to active (no email confirm needed)

---

## Notes

- WeChat OAuth requires China ICP registration — document this in code comments
- The frontend callback URL handler must be implemented in TASK-041 (Frontend Social OAuth)
- For the redirect flow, use `Socialite::driver($provider)->stateless()->redirect()` for SPA compatibility
- Store the target role ('student' or 'enterprise') in the OAuth state parameter so the callback knows what to create
- Token in callback redirect URL should have a short TTL (state token) — frontend must exchange it for full session token via a separate endpoint if security is critical. For simplicity, pass the Sanctum token directly.
