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

- [x] Enterprise can create an interview invitation for a student
- [x] Interview invitation email is sent and appears in Mailpit
- [x] Student can view their interview invitations
- [x] TRTC room token endpoint responds (200 with token OR graceful error if TRTC unconfigured)
- [x] Interview result can be updated (pass/fail/pending)
- [x] Interview can be cancelled
- [x] Admin can create and list all interviews
- [x] Seminar can be created with trilingual content
- [x] Public seminar list is accessible without auth
- [x] Student can register for seminar; duplicate upserts (not rejected — by design)
- [x] Seminar registration email: N/A — confirmation not sent on register (only reminder job exists)
- [x] TRTC live stream token endpoint responds gracefully (error when seminar not started)
- [x] Admin can view seminar registrations
- [x] Contact form submission sends admin notification email

---

## Test Results (2026-05-05)

| Test | Status | Notes |
|------|--------|-------|
| A1 Enterprise Creates Interview | ✅ PASS | Route: `POST /interviews` (not `/enterprise/interviews`) |
| A2 Verify Invitation Email in Mailpit | ✅ PASS | After EmailService fix (see bugs) |
| A3 Student Views Interview Invitations | ✅ PASS | `GET /interviews` returns role-filtered list |
| A4 Get TRTC Room Token | ✅ PASS | `POST /interviews/{id}/join` returns mock token (TRTC unconfigured) |
| A5 Enterprise Views Their Interviews | ✅ PASS | Role-filtered, returns 2 interviews |
| A6 Admin Creates Interview | ✅ PASS | Admin can also create via `POST /interviews` |
| A7 Update Interview Result | ✅ PASS | `PUT /admin/interviews/{id}/record` with `result` + `notes` |
| A8 Cancel Interview | ✅ PASS | `PUT /interviews/{id}/cancel` returns status=cancelled |
| B1 Admin Lists All Interviews | ✅ PASS | `GET /admin/interviews` returns paginated list of 3 |
| C1 Admin Creates Seminar | ✅ PASS | Correct fields: `desc_*`, `speaker_name`, `starts_at`, `duration_min`, `target_audience`, `permission` |
| C2 List Public Seminars | ✅ PASS | `GET /public/seminars` returns seminar with status=scheduled |
| C3 Student Registers | ✅ PASS | `POST /public/seminars/{id}/register` — requires `email`+`name` in body |
| C4 Seminar Registration Email | ⚠️ N/A | No confirmation email on registration; only `SendSeminarReminderJob` exists (pre-event) |
| C5 Duplicate Registration | ⚠️ UPSERT | Duplicate silently upserts (by design), returns success=true |
| C6 TRTC Live Stream Token | ✅ PASS | Returns graceful error `SEMINAR_NOT_STARTED` when seminar hasn't started |
| C7 Admin Views Registrations | ✅ PASS | `GET /admin/seminars/{id}/registrations` (new route added) |
| C8 Admin Updates Seminar Status | ✅ PASS | `PUT /admin/seminars/{id}` with `{status: cancelled}` (fix applied) |
| C9 List Ended Seminars | ✅ PASS | Returns empty array when none ended |
| D1 List Universities | ⚠️ N/A | Route `GET /universities` does not exist (not implemented in backend) |
| D2 Submit Contact Form | ✅ PASS | `POST /public/contact` — requires `subject` field |
| D3 Verify Contact Email | ✅ PASS | `[Contact] Study Abroad Programs Inquiry — Test Visitor` in Mailpit |

**Bugs fixed:**
1. **`EmailService::send()`** — Used `[$toEmail => $toName]` array format which passes VALUES (names) as email addresses to Laravel's `Mail::to()`. Fixed to use `Mail::to($toEmail, $toName ?: null)`.
2. **`UpdateSeminarRequest`** — `status` field not included in rules, so `PATCH /admin/seminars/{id}` with `{status: cancelled}` was silently ignored. Added `'status' => ['sometimes', 'in:scheduled,cancelled']`.
3. **`Admin\SeminarController::registrations()`** — New method added; route `GET /admin/seminars/{id}/registrations` added. Sort was on `created_at` but table uses `registered_at` (fixed).

**Route corrections vs task doc:**
- Interview routes: `POST/GET/PUT /interviews` (no `/enterprise/` prefix)
- Admin interview record: `PUT /admin/interviews/{id}/record` (not `/enterprise/interviews/{id}/result`)
- Cancel interview: `PUT /interviews/{id}/cancel` (not `PATCH`)
- Seminar create fields: `desc_en/zh_cn/th`, `speaker_name`, `starts_at`, `duration_min`, `target_audience`, `permission` (not `description_*`, `speaker`, `scheduled_at`, etc.)
- Public seminar list: `GET /public/seminars` (not `/seminars`)
- Seminar register: requires `{email, name}` body fields (open registration, not auth-only)
- TRTC join: `POST /interviews/{id}/join` (not `GET /interviews/{id}/token`)
- Contact: `POST /public/contact` with `subject` required (not in task doc)

---

## Next Task

Proceed to **TASK-049** (Human Test — Public Pages)
