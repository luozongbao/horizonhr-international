# TASK-048 — Test: Backend Interview & Seminar API

**Type:** AI Test Task  
**Phase:** Test Phase 1 — Backend Core Features  
**Priority:** HIGH  
**Prerequisites:** TASK-046 passed; admin, student, enterprise tokens available  
**Estimated Effort:** 45 min  

---

## Description

Test Interview lifecycle (create, invite, schedule, update status, results) and Seminar lifecycle (create, register, manage, statistics). This covers the most critical business features. TRTC room token generation is tested at the API level (actual video is a human test in TASK-054).

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.C.2 (Interviews), IV.C.3 (Seminars) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Interviews, Seminars sections |
| TRTC Integration | `DOCUMENTS/TRTC_Integration.md` | Section 3 (Interview), Section 4-6 (Live) |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | DB Schema — interviews, seminars |

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
  -d '{"email":"student.test@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token',''))")

ENTERPRISE_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"enterprise.test@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token',''))")
```

---

## Test Steps

### Group A — Interview Lifecycle (Enterprise Creates)

#### A1. Enterprise Creates Interview Invitation

```bash
curl -s -X POST $BASE_URL/enterprise/interviews \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "job_id": 1,
    "scheduled_at": "2026-05-10T10:00:00Z",
    "duration_minutes": 60,
    "notes": "Please prepare a brief self-introduction"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, interview ID and room_id returned; invitation email sent

#### A2. Verify Invitation Email Sent

```bash
curl -s http://10.11.12.30:8025/api/v1/messages | python3 -c "
import sys, json
msgs = json.load(sys.stdin)
for m in msgs.get('messages', []):
    if 'interview' in m.get('Subject','').lower():
        print('Found:', m.get('Subject'))
"
```

**Expected:** Interview invitation email in Mailpit inbox

#### A3. Student Views Interview Invitations

```bash
curl -s $BASE_URL/student/interviews \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing the created interview with status `scheduled`

#### A4. Get TRTC Room Token (Interview)

```bash
INTERVIEW_ID=1

curl -s "$BASE_URL/interviews/$INTERVIEW_ID/token" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`, `trtc_token` and `room_id` returned (or error if TRTC not configured — acceptable, check error is graceful)

#### A5. Enterprise Views Their Interviews

```bash
curl -s $BASE_URL/enterprise/interviews \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing the interview from A1

#### A6. Admin Creates Interview (Admin-Side)

```bash
curl -s -X POST $BASE_URL/admin/interviews \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 1,
    "enterprise_id": 1,
    "job_id": 1,
    "scheduled_at": "2026-05-12T14:00:00Z",
    "duration_minutes": 45,
    "notes": "Admin-created interview"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, interview created

#### A7. Update Interview Result

```bash
curl -s -X PATCH "$BASE_URL/enterprise/interviews/1/result" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "result": "pass",
    "notes": "Excellent communication skills"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, result and notes saved

#### A8. Cancel Interview

```bash
curl -s -X PATCH "$BASE_URL/enterprise/interviews/2/cancel" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"reason": "Scheduling conflict"}' | python3 -m json.tool
```

**Expected:** `success: true`, status updated to `cancelled`

---

### Group B — Admin Interview Management

#### B1. Admin Lists All Interviews

```bash
curl -s "$BASE_URL/admin/interviews?per_page=10" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -30
```

**Expected:** Paginated interview list with student and enterprise info

---

### Group C — Seminar Lifecycle

#### C1. Admin Creates Seminar

```bash
curl -s -X POST $BASE_URL/admin/seminars \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title_en": "Study in China: 2026 Guide",
    "title_zh_cn": "留学中国：2026年指南",
    "title_th": "เรียนในจีน: คู่มือ 2026",
    "description_en": "Comprehensive guide for Southeast Asian students planning to study in China.",
    "description_zh_cn": "为计划赴华留学的东南亚学生提供全面指南。",
    "description_th": "คู่มือครอบคลุมสำหรับนักเรียนเอเชียตะวันออกเฉียงใต้ที่วางแผนเรียนในจีน",
    "speaker": "Prof. Wang Wei",
    "scheduled_at": "2026-05-15T08:00:00Z",
    "duration_minutes": 90,
    "access_type": "public",
    "status": "upcoming"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, seminar ID returned

#### C2. List Public Seminars

```bash
curl -s "$BASE_URL/seminars?status=upcoming&per_page=5" | python3 -m json.tool
```

**Expected:** Array with the created seminar

#### C3. Student Registers for Seminar

```bash
SEMINAR_ID=1

curl -s -X POST "$BASE_URL/seminars/$SEMINAR_ID/register" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`, registration confirmed; reminder email queued

#### C4. Verify Registration Email

```bash
curl -s http://10.11.12.30:8025/api/v1/messages | python3 -c "
import sys, json
msgs = json.load(sys.stdin)
for m in msgs.get('messages', []):
    if 'seminar' in m.get('Subject','').lower():
        print('Found:', m.get('Subject'))
"
```

**Expected:** Seminar registration confirmation email in Mailpit

#### C5. Student Cannot Register Twice

```bash
curl -s -X POST "$BASE_URL/seminars/$SEMINAR_ID/register" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** `success: false`, already registered error

#### C6. Get TRTC Live Stream Token (Seminar)

```bash
curl -s "$BASE_URL/seminars/$SEMINAR_ID/live-token" \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`, viewer token returned (or graceful TRTC-config error)

#### C7. Admin Views Seminar Registrations

```bash
curl -s "$BASE_URL/admin/seminars/$SEMINAR_ID/registrations" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing the student registration

#### C8. Admin Updates Seminar Status

```bash
curl -s -X PATCH "$BASE_URL/admin/seminars/$SEMINAR_ID" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status": "cancelled"}' | python3 -m json.tool
```

**Expected:** `success: true`, status updated

#### C9. List Seminar Playbacks

```bash
curl -s "$BASE_URL/seminars?status=ended&per_page=5" | python3 -m json.tool
```

**Expected:** No error — even if empty array

---

### Group D — University & Contact

#### D1. List Universities

```bash
curl -s "$BASE_URL/universities?per_page=5" | python3 -m json.tool
```

**Expected:** Array of universities (from seed or empty)

#### D2. Submit Contact Form

```bash
curl -s -X POST $BASE_URL/contact \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Visitor",
    "email": "visitor@example.com",
    "phone": "+66812345678",
    "message": "I would like to know more about the study abroad programs"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, notification email sent to admin

#### D3. Verify Contact Email in Mailpit

```bash
curl -s http://10.11.12.30:8025/api/v1/messages | python3 -c "
import sys, json
msgs = json.load(sys.stdin)
for m in msgs.get('messages', []):
    if 'contact' in m.get('Subject','').lower():
        print('Found:', m.get('Subject'))
"
```

**Expected:** Contact notification email in Mailpit

---

## Acceptance Criteria

- [ ] Enterprise can create an interview invitation for a student
- [ ] Interview invitation email is sent and appears in Mailpit
- [ ] Student can view their interview invitations
- [ ] TRTC room token endpoint responds (200 with token OR graceful error if TRTC unconfigured)
- [ ] Interview result can be updated (pass/fail/pending)
- [ ] Interview can be cancelled
- [ ] Admin can create and list all interviews
- [ ] Seminar can be created with trilingual content
- [ ] Public seminar list is accessible without auth
- [ ] Student can register for seminar; duplicate rejected
- [ ] Seminar registration email sent to Mailpit
- [ ] TRTC live stream token endpoint responds gracefully
- [ ] Admin can view seminar registrations
- [ ] Contact form submission sends admin notification email

---

## Next Task

Proceed to **TASK-049** (Human Test — Public Pages)
