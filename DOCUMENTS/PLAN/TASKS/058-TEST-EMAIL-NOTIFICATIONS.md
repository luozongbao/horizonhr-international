# TASK-058 — Test: Email Notifications Comprehensive Check

**Type:** AI Test Task  
**Phase:** Test Phase 4 — Integrations  
**Priority:** HIGH  
**Prerequisites:** TASK-057 passed; Mailpit running at `http://10.11.12.30:8025`  
**Estimated Effort:** 35 min  

---

## Description

Trigger all email notification types and verify they arrive in Mailpit with correct content. This covers the full email system: registration, confirmation, password reset, interview invitation, seminar registration, application status changes, and admin notifications.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.E (Email Notifications) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Email-triggering endpoints |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30/api"
MAILPIT_URL="http://10.11.12.30:8025/api/v1"

# Get tokens
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

# Helper: clear Mailpit and check
clear_mailpit() {
  curl -s -X DELETE "$MAILPIT_URL/messages" > /dev/null
  echo "Mailpit cleared"
}

check_mailpit() {
  local keyword=$1
  sleep 3
  curl -s "$MAILPIT_URL/messages" | python3 -c "
import sys, json
msgs = json.load(sys.stdin)
found = [m for m in msgs.get('messages', []) if '$keyword'.lower() in m.get('Subject','').lower()]
if found:
    print('FOUND:', found[0].get('Subject'))
    print('To:', found[0].get('To', [{}])[0].get('Address',''))
else:
    print('NOT FOUND - subject containing: $keyword')
    print('All subjects:', [m.get('Subject') for m in msgs.get('messages', [])])
"
}
```

---

## Test Steps

### Group A — Registration & Email Confirmation Email

#### A1. Trigger Registration Email

Register a new user to trigger both registration and confirmation emails:

```bash
clear_mailpit

curl -s -X POST $BASE_URL/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Email Test User",
    "email": "emailtest.user@example.com",
    "password": "Test@12345",
    "password_confirmation": "Test@12345",
    "role": "student"
  }' | python3 -m json.tool
```

**Expected:** Registration success

#### A2. Check Confirmation Email in Mailpit

```bash
check_mailpit "confirm"
```

**Expected:** Email confirmation email found with correct recipient

---

### Group B — Password Reset Email

#### B1. Trigger Password Reset

```bash
clear_mailpit

curl -s -X POST $BASE_URL/auth/forgot-password \
  -H "Content-Type: application/json" \
  -d '{"email": "emailtest.user@example.com"}' | python3 -m json.tool
```

#### B2. Check Reset Email

```bash
check_mailpit "password"
```

**Expected:** Password reset email with reset link

---

### Group C — Interview Invitation Email

#### C1. Create Interview (triggers invitation to student)

```bash
clear_mailpit

curl -s -X POST $BASE_URL/enterprise/interviews \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "job_id": 1,
    "scheduled_at": "2026-07-01T10:00:00Z",
    "duration_minutes": 45,
    "notes": "Email notification test interview"
  }' | python3 -m json.tool
```

#### C2. Check Interview Invitation Email

```bash
check_mailpit "interview"
```

**Expected:** Interview invitation email sent to student

---

### Group D — Seminar Registration Email

#### D1. Register Student for Seminar (triggers confirmation email)

```bash
clear_mailpit

SEMINAR_ID=1

curl -s -X POST "$BASE_URL/seminars/$SEMINAR_ID/register" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

#### D2. Check Seminar Registration Email

```bash
check_mailpit "seminar"
```

**Expected:** Seminar registration confirmation email

---

### Group E — Application Status Changed Email

#### E1. Enterprise Updates Application Status

```bash
clear_mailpit

# Update application status to "shortlisted"
APP_ID=1

curl -s -X PATCH "$BASE_URL/enterprise/applications/$APP_ID/status" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "shortlisted", "notes": "You are shortlisted for interview round"}' \
  | python3 -m json.tool
```

#### E2. Check Status Changed Email

```bash
check_mailpit "application"
```

**Expected:** Application status changed email sent to student

---

### Group F — Enterprise Activation Email

#### F1. Admin Activates an Enterprise Account

```bash
clear_mailpit

# Find the enterprise user ID first
ENTERPRISE_USER_ID=$(curl -s "$BASE_URL/admin/users?role=enterprise&status=pending" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  | python3 -c "
import sys, json
d = json.load(sys.stdin)
users = d.get('data', {}).get('data', d.get('data', []))
if isinstance(users, list) and len(users) > 0:
    print(users[0].get('id'))
" 2>/dev/null)

echo "Enterprise User ID: $ENTERPRISE_USER_ID"

if [ -n "$ENTERPRISE_USER_ID" ]; then
  curl -s -X POST "$BASE_URL/admin/users/$ENTERPRISE_USER_ID/activate" \
    -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
fi
```

#### F2. Check Enterprise Activation Email

```bash
check_mailpit "activated\|approved\|enterprise"
```

**Expected:** Enterprise activation notification email sent to enterprise user

---

### Group G — Admin Contact Notification Email

#### G1. Submit Contact Form

```bash
clear_mailpit

curl -s -X POST $BASE_URL/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Email Tester",
    "email": "emailtester@example.com",
    "phone": "+66812345678",
    "message": "Testing the contact form email notification"
  }' | python3 -m json.tool
```

#### G2. Check Admin Contact Notification Email

```bash
check_mailpit "contact"
```

**Expected:** Contact notification email sent to admin

---

### Group H — Email Content Quality Check

#### H1. View Email Content in Mailpit

Open Mailpit web UI: `http://10.11.12.30:8025`

For each received email, visually check:

**Check:**
- [ ] Email has proper HTML formatting (not raw text only)
- [ ] Company logo/branding visible (or placeholder)
- [ ] Recipient name personalized
- [ ] Email contains relevant action buttons (links for confirmation, reset, join interview)
- [ ] Links in emails use correct base URL `http://10.11.12.30`
- [ ] No broken HTML or garbled characters
- [ ] Thai-language emails render Thai characters correctly

#### H2. Check Email Links Work

From Mailpit, copy the confirmation link from the registration email and open in browser.

**Check:**
- [ ] Link opens correct page
- [ ] Link is not expired immediately

---

### Group I — Queue Processing Check

#### I1. Verify Email Queue is Processing

```bash
# Check queue worker status
docker compose -f /home/zongbao/Projects/horizon-international-claude/docker-compose.yml \
  exec backend php artisan queue:size

# Check for failed jobs
docker compose -f /home/zongbao/Projects/horizon-international-claude/docker-compose.yml \
  exec backend php artisan queue:failed
```

**Expected:** Queue size close to 0 (emails processed), no failed jobs

---

## Acceptance Criteria

- [ ] Email confirmation sent on new registration
- [ ] Password reset email contains working reset link
- [ ] Interview invitation email sent when enterprise creates interview
- [ ] Seminar registration confirmation email sent on sign-up
- [ ] Application status changed email sent to student when enterprise updates status
- [ ] Enterprise activation email sent when admin activates account
- [ ] Contact form submission sends notification to admin email
- [ ] Email content is properly formatted HTML (not plain text garble)
- [ ] Links in emails use correct base URL
- [ ] Queue has no failed jobs

---

## Next Task

Proceed to **TASK-059** (AI Test — Security & PDPA)
