# TASK-022: Frontend Authentication Pages

**Phase:** 7 — Frontend: Auth Pages  
**Status:** Pending  
**Depends On:** TASK-021, TASK-004  
**Priority:** HIGH  

---

## Objective

Implement all authentication pages: Login, Register Student, Register Enterprise, Email Verification, Forgot Password, and Reset Password. Each page integrates with the backend auth API and matches the approved visual mockups.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/login.html` — Login page mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/register-student.html` — Student registration mockup
3. `DOCUMENTS/DESIGNS/visual-mockups/register-enterprise.html` — Enterprise registration mockup
4. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 2 (Auth endpoints)
5. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Colors, form styles

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| POST | `/api/auth/login` | Login → returns token + user |
| POST | `/api/auth/register/student` | Student registration |
| POST | `/api/auth/register/enterprise` | Enterprise registration |
| POST | `/api/auth/email/confirm` | Confirm email with token |
| POST | `/api/auth/password/forgot` | Send reset link |
| POST | `/api/auth/password/reset` | Reset with token |
| GET | `/api/auth/me` | Get current user profile |

---

## Deliverables

### API Module
- `frontend/src/api/auth.js`:
  ```js
  export const authApi = {
    login(credentials),
    registerStudent(data),
    registerEnterprise(data),
    confirmEmail(token),
    forgotPassword(email),
    resetPassword(token, password, passwordConfirmation),
    getProfile(),
    logout(),
  }
  ```

### Pages

**`frontend/src/views/auth/LoginPage.vue`**
- Layout: `AuthLayout`
- Form fields: Email, Password
- Remember me checkbox (stores token longer in localStorage)
- "Forgot password?" link → `/password/forgot`
- Social OAuth buttons: Google, Facebook, LinkedIn, WeChat (links to backend OAuth routes)
- Submit → `authApi.login()` → set token via `authStore.setAuth()` → redirect based on role:
  - admin → `/admin/dashboard`
  - student → `/student/dashboard`
  - enterprise → `/enterprise/dashboard`
- Error handling: show inline error message for invalid credentials
- Reference: `visual-mockups/login.html`

**`frontend/src/views/auth/RegisterStudentPage.vue`**
- Layout: `AuthLayout`
- Form fields:
  - Name (required)
  - Email (required)
  - Password (required, min 8 chars)
  - Confirm Password
  - Nationality (required, select from list)
  - I agree to terms (checkbox, required)
- Submit → `authApi.registerStudent()` → success page/message "Please check your email to verify your account"
- Switch link to enterprise registration
- Reference: `visual-mockups/register-student.html`

**`frontend/src/views/auth/RegisterEnterprisePage.vue`**
- Layout: `AuthLayout`
- Form fields:
  - Company Name (required)
  - Contact Name (required)
  - Email (required)
  - Password (required, min 8 chars)
  - Confirm Password
  - Company Industry (select)
  - Company Size (select: 1-50, 51-200, 201-500, 500+)
  - Company Description (textarea)
  - I agree to terms (checkbox, required)
- Submit → `authApi.registerEnterprise()` → message "Your registration is pending admin approval"
- Reference: `visual-mockups/register-enterprise.html`

**`frontend/src/views/auth/EmailVerifyPage.vue`**
- Layout: `AuthLayout`
- On mount: call `authApi.confirmEmail(route.params.token)`
- Show: loading → success card ("Email confirmed! You can now login") OR error card ("Invalid or expired link")
- Redirects to `/login` after 3 seconds on success

**`frontend/src/views/auth/ForgotPasswordPage.vue`**
- Layout: `AuthLayout`
- Form: email input
- Submit → `authApi.forgotPassword(email)` → show "If this email exists, you will receive a reset link"
- Always shows success (prevent email enumeration)

**`frontend/src/views/auth/ResetPasswordPage.vue`**
- Layout: `AuthLayout`
- Form: new password + confirm password
- Token from `route.params.token` (URL)
- Submit → `authApi.resetPassword(token, password, confirmation)` → redirect to `/login` with success message

**`frontend/src/views/auth/OAuthCallbackPage.vue`**
- Layout: `AuthLayout`
- On mount: extract `token` from URL fragment (`#token=...`) or query (`?token=...`)
- If token found: `authStore.setAuth(token)` + fetch profile + redirect to dashboard
- If error: show error message and link back to login

---

## Form Validation

Use Element Plus form validation with rules:
```js
const rules = {
  email: [
    { required: true, message: t('validation.emailRequired') },
    { type: 'email', message: t('validation.emailInvalid') }
  ],
  password: [
    { required: true, message: t('validation.passwordRequired') },
    { min: 8, message: t('validation.passwordMin') }
  ],
  // ...
}
```

All validation messages must support i18n via `t()`.

---

## i18n Keys to Add

```json
"auth": {
  "login": "Login",
  "loginTitle": "Welcome Back",
  "loginSubtitle": "Log in to your account",
  "register": "Register",
  "registerStudent": "Register as Student",
  "registerEnterprise": "Register as Enterprise",
  "email": "Email Address",
  "password": "Password",
  "confirmPassword": "Confirm Password",
  "forgotPassword": "Forgot Password?",
  "name": "Full Name",
  "nationality": "Nationality",
  "companyName": "Company Name",
  "agreeToTerms": "I agree to the Terms and Conditions",
  "socialLogin": "Or continue with",
  "alreadyHaveAccount": "Already have an account?",
  "noAccount": "Don't have an account?",
  "emailVerifying": "Verifying your email...",
  "emailVerified": "Email verified successfully!",
  "emailVerifyFailed": "Verification failed. Invalid or expired link.",
  "resetLinkSent": "If this email is registered, you will receive a password reset link.",
  "passwordReset": "Password has been reset. Please log in.",
  "pendingApproval": "Your registration is pending admin approval. You will be notified by email."
}
```

---

## Acceptance Criteria

- [ ] Login page renders correctly matching `login.html` mockup
- [ ] Login submits credentials, receives token, redirects to correct dashboard by role
- [ ] Login shows error message for invalid credentials
- [ ] Student registration form validates all required fields
- [ ] Student registration submits and shows "check your email" message
- [ ] Enterprise registration form validates all required fields
- [ ] Enterprise registration submits and shows "pending approval" message
- [ ] Email verify page calls API on mount and shows success/error based on response
- [ ] Forgot password always shows success message (no email enumeration)
- [ ] Password reset validates password match before submit
- [ ] OAuth callback page extracts token and redirects to dashboard
- [ ] All form validation messages display in current language via i18n
- [ ] Social OAuth buttons link to `/api/auth/{provider}/redirect` backend routes
- [ ] "Forgot password?" / "Register" / "Login" navigation links work correctly

---

## Notes

- Social OAuth buttons (Google, Facebook, LinkedIn, WeChat) are `<a href="/api/auth/google/redirect">` — they redirect the browser (not AJAX calls)
- After OAuth callback, backend redirects to `/oauth/callback#token=xxx` — the frontend OAuthCallbackPage extracts the token from the URL hash
- Nationality selector: use a static list of common nationalities + countries; does not need API
- Form `loading` state should disable the submit button to prevent double-submission
