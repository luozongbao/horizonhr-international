# TASK-059 — Test: Security & PDPA Compliance

**Type:** AI Test Task  
**Phase:** Test Phase 5 — Security  
**Priority:** CRITICAL  
**Prerequisites:** TASK-058 passed; tokens available for admin, student, enterprise  
**Estimated Effort:** 45 min  

---

## Description

Verify security controls are functioning: authentication required for protected routes, authorization prevents cross-role access, SQL injection attempts are rejected, XSS input is sanitized, rate limiting works on auth endpoints, private data is not exposed, and PDPA-required data handling is in place.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IX.B (PDPA Compliance), VIII (Security) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Auth & Authorization notes |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30/api"

ADMIN_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")

STUDENT_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student.human@example.com","password":"NewTest@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token','FAILED'))")

ENTERPRISE_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"enterprise.human@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token','FAILED'))")
```

---

## Test Steps

### Group A — Authentication Required (Unauthenticated Access Denied)

#### A1. Protected Student Route Without Token

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/student/profile"
```

**Expected:** HTTP 401 Unauthorized

#### A2. Protected Enterprise Route Without Token

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/enterprise/jobs"
```

**Expected:** HTTP 401 Unauthorized

#### A3. Protected Admin Route Without Token

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/admin/users"
```

**Expected:** HTTP 401 Unauthorized

#### A4. Invalid Token Rejected

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/student/profile" \
  -H "Authorization: Bearer invalidtokenXYZ123"
```

**Expected:** HTTP 401 Unauthorized

---

### Group B — Role-Based Access Control (RBAC)

#### B1. Student Cannot Access Admin Routes

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/admin/users" \
  -H "Authorization: Bearer $STUDENT_TOKEN"
```

**Expected:** HTTP 403 Forbidden

#### B2. Enterprise Cannot Access Admin Routes

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/admin/users" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN"
```

**Expected:** HTTP 403 Forbidden

#### B3. Student Cannot Access Enterprise Routes

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/enterprise/jobs" \
  -H "Authorization: Bearer $STUDENT_TOKEN"
```

**Expected:** HTTP 403 Forbidden

#### B4. Enterprise Cannot Access Student Routes

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/student/applications" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN"
```

**Expected:** HTTP 403 Forbidden

---

### Group C — Data Isolation (Users Cannot Access Other Users' Data)

#### C1. Student Cannot Access Another Student's Profile

```bash
# Get student IDs from admin
STUDENT_IDS=$(curl -s "$BASE_URL/admin/users?role=student" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  | python3 -c "
import sys, json
d = json.load(sys.stdin)
users = d.get('data', {}).get('data', d.get('data', []))
ids = [u.get('id') for u in users[:3] if isinstance(u, dict)]
print(' '.join(map(str, ids)))
")

# Try to access another student's profile using student token
for id in $STUDENT_IDS; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/student/profile/$id" \
    -H "Authorization: Bearer $STUDENT_TOKEN")
  echo "Profile $id: $STATUS"
done
```

**Expected:** Only own profile returns 200; others return 403 or 404

#### C2. Private Resumes Not Accessible by Enterprise

```bash
# Get a student's admin-only resume ID
RESUME_ID=$(curl -s "$BASE_URL/admin/resumes?visibility=admin" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  | python3 -c "
import sys, json
d = json.load(sys.stdin)
items = d.get('data', {}).get('data', d.get('data', []))
if isinstance(items, list) and len(items) > 0:
    print(items[0].get('id'))
")

echo "Private resume ID: $RESUME_ID"

if [ -n "$RESUME_ID" ]; then
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/enterprise/resumes/$RESUME_ID" \
    -H "Authorization: Bearer $ENTERPRISE_TOKEN")
  echo "Enterprise access to private resume: $STATUS"
fi
```

**Expected:** HTTP 403 or 404 for admin-only resumes when accessed by enterprise

---

### Group D — Input Validation (SQL Injection & XSS)

#### D1. SQL Injection in Login

```bash
curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@horizonhr.com'\'' OR 1=1 --","password": "anything"}' \
  | python3 -m json.tool
```

**Expected:** HTTP 422 validation error or 401 Unauthorized — NOT a successful login

#### D2. SQL Injection in Search Query

```bash
curl -s -w "\nHTTP: %{http_code}" \
  "$BASE_URL/student/jobs?search=Developer%27%3B%20DROP%20TABLE%20jobs%3B%20--" \
  -H "Authorization: Bearer $STUDENT_TOKEN"
```

**Expected:** HTTP 200 with empty results or 422 validation error — NOT a 500 error

#### D3. XSS in Profile Bio Field

```bash
curl -s -X PUT $BASE_URL/student/profile \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "bio": "<script>alert(\"XSS\")</script> Legitimate bio content"
  }' | python3 -m json.tool
```

Then retrieve the profile to check stored value:

```bash
curl -s $BASE_URL/student/profile \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  | python3 -c "
import sys, json
d = json.load(sys.stdin)
bio = d.get('data', {}).get('bio', '')
if '<script>' in bio:
    print('FAIL: XSS not sanitized:', bio[:100])
else:
    print('PASS: XSS stripped or escaped. Stored:', bio[:100])
"
```

**Expected:** Script tag stripped or HTML-encoded — not stored as raw `<script>`

#### D4. XSS in Contact Form

```bash
curl -s -X POST $BASE_URL/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "<img src=x onerror=alert(1)>",
    "email": "xsstest@example.com",
    "message": "Normal message"
  }' | python3 -m json.tool
```

**Expected:** HTTP 200 (contact submitted) or 422 validation error; XSS not executed

---

### Group E — Rate Limiting

#### E1. Auth Rate Limiting (Multiple Failed Logins)

```bash
echo "Testing rate limit on login..."
for i in {1..8}; do
  STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X POST $BASE_URL/auth/login \
    -H "Content-Type: application/json" \
    -d '{"email":"ratelimit@example.com","password":"wrong"}')
  echo "Attempt $i: $STATUS"
done
```

**Expected:** First few: 422 (validation error) or 401; After 5-6 attempts: 429 (Too Many Requests)

---

### Group F — Password Security

#### F1. Passwords Not Returned in API Responses

```bash
curl -s $BASE_URL/auth/user \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  | python3 -c "
import sys, json
d = json.load(sys.stdin)
user = d.get('data', d.get('user', {}))
if 'password' in user:
    print('FAIL: Password field present in response')
elif 'password_hash' in str(d):
    print('FAIL: Password hash present in response')
else:
    print('PASS: No password field in response')
"
```

**Expected:** No password field in user API response

#### F2. Weak Password Rejected on Registration

```bash
curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Weak Pass User",
    "email": "weakpass@example.com",
    "password": "123",
    "password_confirmation": "123",
    "role": "student"
  }' | python3 -m json.tool
```

**Expected:** HTTP 422 validation error: password too short / doesn't meet requirements

---

### Group G — HTTP Security Headers

#### G1. Check Response Headers

```bash
curl -s -D - -o /dev/null "$BASE_URL/health" | head -30
```

**Check for presence of:**
- `X-Content-Type-Options: nosniff`
- `X-Frame-Options: DENY` or `SAMEORIGIN`
- `Content-Security-Policy` header (optional but recommended)

**Expected:** At minimum `X-Content-Type-Options` header present

---

### Group H — PDPA Compliance Checks

#### H1. Data Deletion / Account Deletion

```bash
# Student requests account deletion
curl -s -X DELETE $BASE_URL/student/account \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"confirmation": "DELETE"}' | python3 -m json.tool
```

**Expected:** `success: true` OR endpoint returns 404/501 if not yet implemented (note: document result)

#### H2. Privacy Policy Page Accessible

```bash
curl -s -w "\nHTTP: %{http_code}" "$BASE_URL/pages/privacy-policy"
```

**Expected:** HTTP 200 with privacy policy content

---

## Acceptance Criteria

- [ ] All protected routes return 401 without a token
- [ ] Student cannot access admin or enterprise routes (403)
- [ ] Enterprise cannot access admin or student routes (403)
- [ ] Student cannot access another student's private profile
- [ ] Private resumes inaccessible to enterprise role
- [ ] SQL injection attempts do not cause 500 errors or unauthorized data return
- [ ] XSS in profile/contact fields is stripped or escaped before storage
- [ ] Rate limiting activates after 5-6 failed auth attempts (429)
- [ ] Passwords never returned in API responses
- [ ] Weak passwords rejected at registration
- [ ] Security headers present (X-Content-Type-Options at minimum)

---

## Notes

- Account deletion (H1) may not be fully implemented per MVP scope — document actual result
- Privacy policy page requirement depends on CMS setup from TASK-047/053
- If rate limiting returns 429 at a different threshold, document the actual threshold

---

## Next Task

Proceed to **TASK-060** (AI Test — Performance & SEO)
