# TASK-050 — [HUMAN TEST] Frontend: Authentication Flows

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 2 — Frontend Auth  
**Priority:** CRITICAL  
**Prerequisites:** TASK-049 passed; Mailpit accessible at `http://10.11.12.30:8025`  
**Estimated Effort:** 40 min  

---

## Description

Manually test all authentication flows through the frontend UI: Student registration, Enterprise registration, email confirmation, login, logout, and password reset. Verify that multi-role login works correctly and UI feedback (errors, success messages) is clear.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B (User System), Registration Workflow |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Section 2 (Auth Endpoints) |

---

## Test Accounts to Create During This Test

| Role | Email | Password |
|------|-------|----------|
| Student | `student.human@example.com` | `Test@12345` |
| Enterprise | `enterprise.human@example.com` | `Test@12345` |

Keep Mailpit open in a separate tab: `http://10.11.12.30:8025`

---

## Test Steps

### Group A — Student Registration & Email Confirmation

#### A1. Open Registration Page

Navigate to `http://10.11.12.30` → Click "Register" → Select "Student".

Or navigate directly to `/register` or `/register/student`.

**Check:**
- [✅] Registration form visible with fields: Name, Email, Password, Confirm Password
- [✅] Role selector visible (Student / Enterprise)

#### A2. Fill and Submit Student Registration

Fill in the form:
```
Name: Human Test Student
Email: student.human@example.com
Password: Test@12345
Confirm Password: Test@12345
Role: Student
```

Click "Register".

**Check:**
- [✅] Form validates required fields
- [✅] Success message shows: "Registration successful. Please check your email to confirm."
- [✅] Redirected to appropriate waiting page

#### A3. Check Email Confirmation in Mailpit

Open `http://10.11.12.30:8025` → Find "Email Confirmation" or "Verify Your Email" email.

**Check:**
- [✅] Email received with correct recipient address
- [✅] Email contains a confirmation link
- [✅] Email is in readable format (HTML or plain text)

#### A4. Click Email Confirmation Link

Click the confirmation link in the email.

**Check:**
- [✅] Browser opens to confirmation page
- [✅] Success message: "Email confirmed! You can now log in."
- [✅] Link to login page

---

### Group B — Student Login & Logout

#### B1. Login as Student

Navigate to `/login`.

Fill:
```
Email: student.human@example.com
Password: Test@12345
```

**Check:**
- [✅] Login succeeds
- [✅] Redirected to Student Dashboard (`/student/dashboard`)
- [✅] Student name visible in header
- [✅] Navigation shows student-specific menu items (My Profile, My Applications, Seminars, etc.)

#### B2. Wrong Password Shows Error

Try to login with wrong password:
```
Email: student.human@example.com
Password: WrongPassword
```

**Check:**
- [✅] Error message shown: "Invalid email or password" (or similar)
- [✅] No redirect to dashboard

#### B3. Logout

Click logout button or user menu → "Logout".

**Check:**
- [✅] Redirected to home page or login page
- [✅] Dashboard no longer accessible (redirect to login if visited directly)

---

### Group C — Enterprise Registration

#### C1. Register as Enterprise

Navigate to `/register` → Select "Enterprise".

Fill:
```
Company Name: Human Test Corp
Email: enterprise.human@example.com
Password: Test@12345
Confirm Password: Test@12345
```

Click "Register".

**Check:**
- [✅] Success message: "Registration submitted. Please wait for admin approval." (or similar)
- [✅] Email confirmation sent

#### C2. Enterprise Cannot Login Before Activation

Try to login immediately:
```
Email: enterprise.human@example.com
Password: Test@12345
```

**Check:**
- [✅] Login fails with message: "Account pending approval" or similar
- [✅] Not redirected to enterprise dashboard

#### C3. Admin Activates Enterprise Account

In another browser tab, login as admin (`admin@horizonhr.com` / `Admin@12345`).  
Navigate to Admin → User Management → Find enterprise.human@example.com → Activate.

**Check:**
- [✅] Admin can see the pending enterprise account
- [✅] Activation action succeeds
- [✅] Status changes to "active"

#### C4. Enterprise Logs In After Activation

Return to original tab. Retry login:
```
Email: enterprise.human@example.com
Password: Test@12345
```

**Check:**
- [✅] Login succeeds
- [✅] Redirected to Enterprise Dashboard
- [✅] Enterprise navigation visible (Dashboard, My Jobs, Talent Search, Interviews)

---

### Group D — Password Reset Flow

#### D1. Request Password Reset

From login page, click "Forgot Password?".

Enter: `student.human@example.com`

**Check:**
- [✅] Success message: "Password reset email sent"

#### D2. Get Reset Link from Mailpit

Open `http://10.11.12.30:8025` → Find "Password Reset" email.

**Check:**
- [✅] Email received
- [✅] Contains a unique reset link

#### D3. Reset Password via Link

Click the reset link. Fill new password:
```
New Password: NewTest@12345
Confirm Password: NewTest@12345
```

**Check:**
- [✅] Success message: "Password updated successfully"
- [✅] Can now login with new password: `NewTest@12345`

#### D4. Login with New Password

```
Email: student.human@example.com
Password: NewTest@12345
```

**Check:**
- [✅] Login succeeds

---

### Group E — Form Validation UI

#### E1. Registration Form Validation

Navigate to `/register`. Submit empty form.

**Check:**
- [✅] Validation errors shown under each required field
- [✅] "Email is required", "Password is required", etc.

#### E2. Password Mismatch Validation

Fill password fields with different values:
```
Password: Test@12345
Confirm Password: Different@12345
```

**Check:**
- [✅] Error: "Passwords do not match"

#### E3. Invalid Email Format

Enter: `not-an-email`

**Check:**
- [✅] Error: "Invalid email format"

---

## Acceptance Criteria

- [✅] Student registration form works, sends confirmation email
- [✅] Email confirmation link activates account
- [✅] Student can login after confirmation; redirected to student dashboard
- [✅] Wrong password shows clear error message
- [✅] Logout revokes session; dashboard becomes inaccessible
- [✅] Enterprise registration sends pending approval message
- [✅] Enterprise cannot login before admin activation
- [✅] Admin can activate enterprise accounts in User Management
- [✅] Enterprise can login after activation; goes to enterprise dashboard
- [✅] Password reset: form → email received → link opens → new password set → login works
- [✅] Form validation shows field-level errors for empty/invalid inputs

---

## Next Task

Proceed to **TASK-051** (Human Test — Student Portal Workflow)
