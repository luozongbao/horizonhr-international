# TASK-045 — Test: Backend Auth API

**Type:** AI Test Task  
**Phase:** Test Phase 1 — Backend Auth  
**Priority:** CRITICAL  
**Prerequisites:** TASK-044 passed (environment verified)  
**Estimated Effort:** 45 min  

---

## Description

Test all authentication API endpoints including registration, login, email confirmation, password reset, token refresh, logout — for all three user roles (admin, student, enterprise). Also verify middleware protection on guarded routes.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B (User System) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Section 2 (Authentication) |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | Module Architecture — Auth |

---

## Environment Setup

```bash
BASE_URL="http://10.11.12.30/api"
```

Store the admin token from TASK-044 Step 5 for use in tests.

---

## Test Steps

### Group A — Registration

#### A1. Register New Student Account

```bash
curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student.test@example.com",
    "password": "Test@12345",
    "password_confirmation": "Test@12345",
    "role": "student",
    "name": "Test Student"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, registration confirmation; email verification sent

#### A2. Register New Enterprise Account

```bash
curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "enterprise.test@example.com",
    "password": "Test@12345",
    "password_confirmation": "Test@12345",
    "role": "enterprise",
    "company_name": "Test Corp Ltd"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, registration confirmation

#### A3. Reject Duplicate Email Registration

```bash
curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student.test@example.com",
    "password": "Test@12345",
    "password_confirmation": "Test@12345",
    "role": "student"
  }' | python3 -m json.tool
```

**Expected:** `success: false`, error about duplicate email

#### A4. Reject Weak Password

```bash
curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "weak@example.com",
    "password": "123",
    "password_confirmation": "123",
    "role": "student"
  }' | python3 -m json.tool
```

**Expected:** Validation error (password too short/weak)

---

### Group B — Login

#### B1. Admin Login (Already Seeded)

```bash
ADMIN_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")
echo "Admin token: $ADMIN_TOKEN"
```

**Expected:** Token string printed

#### B2. Login with Wrong Password

```bash
curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"WrongPass"}' | python3 -m json.tool
```

**Expected:** `success: false`, invalid credentials error

#### B3. Get Authenticated User Profile

```bash
curl -s $BASE_URL/auth/user \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
```

**Expected:** Admin user data including `role: "admin"`, `email: "admin@horizonhr.com"`

#### B4. Unprotected Access Denied

```bash
curl -s $BASE_URL/auth/user | python3 -m json.tool
```

**Expected:** `401 Unauthorized`

---

### Group C — Password Reset Flow

#### C1. Request Password Reset

```bash
curl -s -X POST $BASE_URL/auth/password/forgot \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com"}' | python3 -m json.tool
```

**Expected:** `success: true`, email sent message

#### C2. Verify Reset Email in Mailpit

```bash
curl -s http://10.11.12.30:8025/api/v1/messages | python3 -c "
import sys, json
msgs = json.load(sys.stdin)
for m in msgs.get('messages', []):
    print(m.get('Subject',''), m.get('To',''))
"
```

**Expected:** Password reset email visible in Mailpit

---

### Group D — Logout & Token Invalidation

#### D1. Logout

```bash
curl -s -X POST $BASE_URL/auth/logout \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`, logged out message

#### D2. Token Invalid After Logout

```bash
curl -s $BASE_URL/auth/user \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
```

**Expected:** `401 Unauthorized` (token revoked)

---

### Group E — Role-Based Access Control

Re-login as admin before these tests:

```bash
ADMIN_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")
```

#### E1. Admin Can Access Admin Routes

```bash
curl -s "$BASE_URL/admin/users?per_page=5" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -20
```

**Expected:** `success: true`, paginated user list

#### E2. Student Cannot Access Admin Routes

First activate a test student (or use admin to activate), then login:
```bash
# Activate test student via admin API
curl -s -X PATCH "$BASE_URL/admin/users/2/status" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"active"}' | python3 -m json.tool
```

```bash
STUDENT_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student.test@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token','FAIL'))")

curl -s "$BASE_URL/admin/users" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** `403 Forbidden`

---

## Acceptance Criteria

- [x] Student registration creates account, sends confirmation email
- [x] Enterprise registration creates account pending activation
- [x] Duplicate email registration is rejected with proper error
- [x] Weak password registration is rejected with validation error
- [x] Admin login returns valid JWT token
- [x] Wrong password login returns 401/422 error
- [x] `GET /api/auth/me` returns user data with valid token
- [x] `GET /api/auth/me` returns 401 without token
- [x] Password reset email is sent and received in Mailpit
- [x] Logout invalidates token (subsequent requests return 401)
- [x] Admin can access `GET /api/admin/users`
- [x] Student token returns 403 on admin routes

---

## Test Results (2026-05-04)

| Test | Status | Notes |
|------|--------|-------|
| A1 Register Student | ✅ PASS | ID=5, pending, email unverified |
| A2 Register Enterprise | ✅ PASS | ID=6, requires both `name` + `company_name` |
| A3 Reject Duplicate Email | ✅ PASS | `EMAIL_ALREADY_REGISTERED` error code |
| A4 Reject Weak Password | ✅ PASS | Validation error returned |
| B1 Admin Login | ✅ PASS | Returns token successfully |
| B2 Wrong Password | ✅ PASS | HTTP 401 `INVALID_CREDENTIALS` |
| B3 Get Auth Profile | ✅ PASS | Returns admin user data |
| B4 Unprotected Access Denied | ✅ PASS | HTTP 401 `Unauthenticated.` |
| C1 Request Password Reset | ✅ PASS | HTTP 200, job queued |
| C2 Reset Email in Mailpit | ✅ PASS | Email received in Mailpit |
| D1 Logout | ✅ PASS | HTTP 200 success |
| D2 Token Invalidated | ✅ PASS | HTTP 401 after logout |
| E1 Admin Access Admin Routes | ✅ PASS | HTTP 200, user list returned |
| E2 Student Access Admin Routes | ✅ PASS | HTTP 403 `FORBIDDEN` |

**Bugs found & fixed:**
1. PHP 8.2 trait property conflict in all 10 Job classes (`public string $queue` conflicted with `Queueable` trait) — removed property, use `$this->onQueue('emails')` in constructor
2. `Authenticate.php` middleware called `route('login')` which doesn't exist → fixed to return `null` so API returns JSON 401
3. SMTP DB settings had `smtp_port=587` but Mailpit runs on port `1025` — updated DB settings

---

## Next Task

After all checks pass → Proceed to **TASK-046** (Backend Core Business API Tests)
