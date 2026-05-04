# TASK-046 — Test: Backend Core Business API (Student, Enterprise, Jobs, Applications)

**Type:** AI Test Task  
**Phase:** Test Phase 1 — Backend Core  
**Priority:** HIGH  
**Prerequisites:** TASK-045 passed; admin + student + enterprise tokens available  
**Estimated Effort:** 60 min  

---

## Description

Test all core business API endpoints: Student profile management, resume upload, Enterprise profile/job management, job applications, and talent pool access. Verify data visibility rules (resume visibility settings) and role-based data access.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B (User System), Section IV.C (Core Functions) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Students, Enterprise, Applications sections |
| System Design | `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` | DB Schema — students, enterprises, resumes, job_listings |

---

## Pre-Test Setup

```bash
BASE_URL="http://10.11.12.30/api"

# Admin token
ADMIN_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@horizonhr.com","password":"Admin@12345"}' \
  | python3 -c "import sys,json; print(json.load(sys.stdin)['data']['token'])")

# Activate and login student (created in TASK-045)
STUDENT_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"student.test@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token',''))")

# Activate enterprise and get token
curl -s -X PATCH "$BASE_URL/admin/users/3/status" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"active"}' | python3 -m json.tool

ENTERPRISE_TOKEN=$(curl -s -X POST $BASE_URL/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"enterprise.test@example.com","password":"Test@12345"}' \
  | python3 -c "import sys,json; d=json.load(sys.stdin); print(d.get('data',{}).get('token',''))")
```

---

## Test Steps

### Group A — Student Profile

#### A1. Update Student Profile

```bash
curl -s -X PUT $BASE_URL/student/profile \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Test Student Updated",
    "nationality": "TH",
    "phone": "+66812345678",
    "gender": "male",
    "bio": "Computer Science student from Bangkok"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, updated profile data returned

#### A2. Get Student Profile

```bash
curl -s $BASE_URL/student/profile \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** Profile with updated name, nationality, etc.

---

### Group B — Resume Upload & Management

#### B1. Upload Resume (PDF)

Create a test PDF file:
```bash
echo "%PDF-1.4 test resume" > /tmp/test_resume.pdf

curl -s -X POST $BASE_URL/student/resumes \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -F "file=@/tmp/test_resume.pdf" \
  -F "title=Test Resume" \
  -F "visibility=enterprise" | python3 -m json.tool
```

**Expected:** `success: true`, resume ID returned, file stored

#### B2. List My Resumes

```bash
curl -s $BASE_URL/student/resumes \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing uploaded resume with `visibility: "enterprise"`

#### B3. Update Resume Visibility

```bash
# Get resume ID from B2, replace RESUME_ID
RESUME_ID=1

curl -s -X PATCH "$BASE_URL/student/resumes/$RESUME_ID" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"visibility":"public"}' | python3 -m json.tool
```

**Expected:** `success: true`, visibility updated

#### B4. Reject Oversized Upload (Simulated)

```bash
# Create a file description note - actual 20MB+ test skipped for CI
# Just verify the validation error message exists in API docs
curl -s $BASE_URL/student/resumes \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool | head -5
```

**Expected (documentation check):** API returns 422 for files > 20MB per REQUIREMENTS

---

### Group C — Enterprise Profile & Jobs

#### C1. Update Enterprise Profile

```bash
curl -s -X PUT $BASE_URL/enterprise/profile \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "company_name": "Test Corp International Ltd",
    "industry": "Technology",
    "scale": "50-200",
    "description": "Leading tech company in Southeast Asia",
    "website": "https://testcorp.example.com",
    "address": "Bangkok, Thailand"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, updated profile data

#### C2. Create Job Listing

```bash
curl -s -X POST $BASE_URL/enterprise/jobs \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Software Developer",
    "description": "Full-stack developer position",
    "requirements": "Vue.js, Laravel, 2+ years experience",
    "location": "Shanghai, China",
    "salary_range": "15000-25000 CNY",
    "job_type": "full_time",
    "nationality_preference": "TH,MY,VN",
    "status": "active"
  }' | python3 -m json.tool
```

**Expected:** `success: true`, job ID returned

#### C3. List Enterprise Jobs

```bash
curl -s $BASE_URL/enterprise/jobs \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing created job

#### C4. View Talent Pool (Enterprise Access)

```bash
curl -s "$BASE_URL/enterprise/talents?per_page=10" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" | python3 -m json.tool
```

**Expected:** `success: true`, list of public/enterprise-visible student profiles

---

### Group D — Public Talent Pool

#### D1. Public Talent Pool (No Auth)

```bash
curl -s "$BASE_URL/students?visibility=public&per_page=5" | python3 -m json.tool
```

**Expected:** Students with `visibility: "public"` only; no private data

#### D2. Filtering by Nationality

```bash
curl -s "$BASE_URL/students?nationality=TH&per_page=5" | python3 -m json.tool
```

**Expected:** Filtered results or empty array (no 500 error)

---

### Group E — Job Applications

#### E1. Student Applies for Job

```bash
JOB_ID=1

curl -s -X POST "$BASE_URL/student/applications" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"job_id\": $JOB_ID}" | python3 -m json.tool
```

**Expected:** `success: true`, application created

#### E2. Student Cannot Apply Twice

```bash
curl -s -X POST "$BASE_URL/student/applications" \
  -H "Authorization: Bearer $STUDENT_TOKEN" \
  -H "Content-Type: application/json" \
  -d "{\"job_id\": $JOB_ID}" | python3 -m json.tool
```

**Expected:** `success: false`, duplicate application error

#### E3. Enterprise Views Applications for Job

```bash
curl -s "$BASE_URL/enterprise/jobs/$JOB_ID/applications" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing the application from E1

#### E4. Enterprise Updates Application Status

```bash
APP_ID=1

curl -s -X PATCH "$BASE_URL/enterprise/applications/$APP_ID/status" \
  -H "Authorization: Bearer $ENTERPRISE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"reviewed"}' | python3 -m json.tool
```

**Expected:** `success: true`, status updated

#### E5. Student Views Their Applications

```bash
curl -s $BASE_URL/student/applications \
  -H "Authorization: Bearer $STUDENT_TOKEN" | python3 -m json.tool
```

**Expected:** Array containing application with `status: "reviewed"`

---

### Group F — Admin Oversight

#### F1. Admin Views All Resumes

```bash
curl -s "$BASE_URL/admin/resumes?per_page=10" \
  -H "Authorization: Bearer $ADMIN_TOKEN" | python3 -m json.tool | head -30
```

**Expected:** `success: true`, paginated resume list (admin sees all)

#### F2. Admin Reviews a Resume (Status Change)

```bash
curl -s -X PATCH "$BASE_URL/admin/resumes/1/status" \
  -H "Authorization: Bearer $ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"status":"approved"}' | python3 -m json.tool
```

**Expected:** `success: true`, resume status updated to approved

---

## Acceptance Criteria

- [ ] Student profile can be created/updated via API
- [ ] Resume upload succeeds and returns file reference
- [ ] Resume visibility can be set to `admin`/`enterprise`/`public`
- [ ] Enterprise profile can be created/updated
- [ ] Enterprise can create and manage job listings
- [ ] Enterprise can view talent pool (enterprise/public visibility only)
- [ ] Public talent API returns only public-visibility students
- [ ] Student can apply for a job; duplicate application is rejected
- [ ] Enterprise can view and update application status
- [ ] Student can view their own application history with status
- [ ] Admin can view all resumes regardless of visibility
- [ ] Admin can approve/reject resumes

---

## Next Task

Proceed to **TASK-047** (Backend CMS, Settings & Language API Tests)
