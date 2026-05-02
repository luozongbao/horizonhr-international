# TASK-004: Backend Auth Module

**Phase:** 2 — Backend: Authentication  
**Status:** Pending  
**Depends On:** TASK-003  
**Priority:** HIGH  

---

## Objective

Implement the complete email/password authentication system for all three user roles (admin, student, enterprise). This includes registration, login, JWT (Sanctum) token issuance, email confirmation workflow, and password reset via email link. This is the foundation that all other backend modules depend on.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Sections 2.1–2.5, 2.7
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.1–1.2.4, 1.2.18–1.2.19, 2.3.1
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B (user accounts), IV.C (registration workflow)

---

## API Endpoints to Implement

All from `API_DOCUMENTATION.md` Sections 2.1–2.5 and 2.7:

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/auth/register` | None | Register student or enterprise |
| POST | `/api/auth/login` | None | Login, return Bearer token |
| POST | `/api/auth/logout` | Bearer | Revoke current token |
| POST | `/api/auth/refresh` | Bearer | Refresh token |
| GET | `/api/auth/me` | Bearer | Get current user profile |
| POST | `/api/auth/forgot-password` | None | Send password reset email |
| POST | `/api/auth/reset-password` | None | Reset password via token |
| POST | `/api/auth/confirm-email` | None | Confirm email via token |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Auth/AuthController.php`
  - `register()` — validate, create user + role profile, send confirmation email, return 201
  - `login()` — validate, check status, create token, return token + user data
  - `logout()` — revoke current token
  - `refresh()` — revoke and reissue token
  - `me()` — return authenticated user with role profile
  - `forgotPassword()` — create `password_resets` row, dispatch email job
  - `resetPassword()` — validate token, update password, mark token used
  - `confirmEmail()` — validate token, mark email confirmed, activate student; enterprise stays pending

### Form Requests
- `app/Http/Requests/Auth/RegisterRequest.php`
  - email, password (min 8, mixed case + number + symbol), password_confirmation, role (student|enterprise), name, nationality (required for student), phone
- `app/Http/Requests/Auth/LoginRequest.php`
  - email, password
- `app/Http/Requests/Auth/ForgotPasswordRequest.php`
  - email
- `app/Http/Requests/Auth/ResetPasswordRequest.php`
  - token, email, password, password_confirmation
- `app/Http/Requests/Auth/ConfirmEmailRequest.php`
  - token, email

### Middleware
- `app/Http/Middleware/CheckUserStatus.php` — after Sanctum auth, verify user status is 'active'; return 403 if suspended/deleted
- `app/Http/Middleware/RoleMiddleware.php` — verify user role matches required role; usage: `->middleware('role:admin')`

### Services
- `app/Services/AuthService.php`
  - `createStudent(array $data): User` — create user + student row in transaction
  - `createEnterprise(array $data): User` — create user + enterprise row in transaction
  - `generateEmailConfirmationToken(User $user): string`
  - `generatePasswordResetToken(User $user): string`

### Jobs / Mail
- `app/Jobs/SendEmailConfirmationJob.php` — queued job to send confirmation email
- `app/Jobs/SendPasswordResetJob.php` — queued job to send password reset email
- `app/Mail/EmailConfirmationMail.php` — uses `email-templates/student-verify-email.html` as view
- `app/Mail/PasswordResetMail.php` — uses `email-templates/password-reset.html` as view

### Routes
In `routes/api.php`, add public auth group:
```php
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('confirm-email', [AuthController::class, 'confirmEmail']);
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('me', [AuthController::class, 'me']);
    });
});
```

---

## Registration Workflow Logic

### Student Registration:
1. Validate request
2. Create `users` row (role=student, status=pending, email_verified=false)
3. Create `students` row with name, nationality, phone
4. Create `email_confirmations` row (token = random 64-char hex, expires_at = +24h)
5. Dispatch `SendEmailConfirmationJob`
6. Return 201 with message "Registration successful. Please check your email to activate your account."

### Enterprise Registration:
1. Validate request
2. Create `users` row (role=enterprise, status=pending, enterprise_status=pending)
3. Create `enterprises` row with company_name
4. Create `email_confirmations` row
5. Dispatch `SendEmailConfirmationJob`
6. Return 201 with message "Registration received. After email confirmation, your account will be reviewed by our team."

### Email Confirmation:
1. Find token in `email_confirmations` — must be unused and not expired
2. Mark `confirmed_at = now()` on the token
3. Set `users.email_verified = true`
4. If role = student: set `users.status = active`
5. If role = enterprise: keep `users.status = pending` (needs admin activation)
6. Return success

### Login:
1. Find user by email (must exist, must not be deleted)
2. Verify password
3. Check `email_verified = true` — if not, return 401 with code `EMAIL_NOT_VERIFIED`
4. Check status is not `suspended` or `deleted`
5. Update `last_login_at` and `last_login_ip`
6. Issue Sanctum token
7. Return token + user object with role profile

---

## Response Shapes

**Login Success:**
```json
{
  "success": true,
  "data": {
    "token": "...",
    "token_type": "Bearer",
    "user": {
      "id": 1,
      "email": "user@example.com",
      "role": "student",
      "status": "active",
      "prefer_lang": "en",
      "profile": { ... }
    }
  }
}
```

**Error codes to use:** `INVALID_CREDENTIALS`, `EMAIL_NOT_VERIFIED`, `ACCOUNT_SUSPENDED`, `ACCOUNT_NOT_FOUND`, `TOKEN_EXPIRED`, `TOKEN_INVALID`, `EMAIL_ALREADY_REGISTERED`

---

## Acceptance Criteria

- [ ] `POST /api/auth/register` (student) creates user + student rows, returns 201
- [ ] `POST /api/auth/register` (enterprise) creates user + enterprise rows, returns 201
- [ ] Registration sends email confirmation (job dispatched to queue)
- [ ] `POST /api/auth/confirm-email` activates student account
- [ ] `POST /api/auth/confirm-email` keeps enterprise in pending status
- [ ] `POST /api/auth/login` returns Bearer token for active users
- [ ] Login fails with `EMAIL_NOT_VERIFIED` error if email not confirmed
- [ ] Login fails with `ACCOUNT_SUSPENDED` error if suspended
- [ ] `GET /api/auth/me` returns user + profile with valid token
- [ ] `POST /api/auth/logout` revokes token (subsequent requests return 401)
- [ ] `POST /api/auth/forgot-password` creates reset token, dispatches email job
- [ ] `POST /api/auth/reset-password` updates password, marks token used
- [ ] Expired tokens are rejected
- [ ] Password validation enforces minimum complexity
- [ ] Rate limiting: auth endpoints 5 req/min per IP
- [ ] `CheckUserStatus` middleware blocks suspended users
- [ ] `RoleMiddleware` rejects wrong-role requests with 403

---

## Notes

- Admin accounts are NOT created via registration — only via AdminSeeder or admin creation endpoint (TASK-006)
- Do NOT store raw tokens — store only the hashed Sanctum token in `personal_access_tokens`
- Email templates will be proper HTML in TASK-017; for now, use simple text emails
- The `password_resets` table uses a separate table (not Laravel's default) — ensure the custom table is used
- PDPA: log consent at registration time in `consent_logs` table
