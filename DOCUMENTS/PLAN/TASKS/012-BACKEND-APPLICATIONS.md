# TASK-012: Backend Job Applications Module

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-010, TASK-011  
**Priority:** HIGH  

---

## Objective

Implement job application workflow: students apply to jobs, enterprises review applications and change status, and notifications are sent to both parties.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.8 (Applications)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.11, 2.3.4, 2.3.5
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.2 (Student: Job viewing, submit resumes), IV.B.3 (Enterprise: view resumes, filter)

---

## API Endpoints to Implement

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/student/applications` | Student | List own applications |
| POST | `/api/student/applications` | Student | Apply to a job |
| DELETE | `/api/student/applications/{id}` | Student | Withdraw application |
| GET | `/api/enterprise/applications` | Enterprise | Applications for own jobs |
| GET | `/api/enterprise/applications/{id}` | Enterprise | Application detail |
| PUT | `/api/enterprise/applications/{id}/status` | Enterprise | Update application status |
| GET | `/api/admin/applications` | Admin | All applications |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Student/ApplicationController.php`
  - `index()` — list own applications with job + enterprise info
  - `store(StoreApplicationRequest $request)` — apply to job
  - `destroy(int $id)` — withdraw (set status=withdrawn); only if status=pending

- `app/Http/Controllers/Enterprise/ApplicationController.php`
  - `index(Request $request)` — applications for own jobs; filter by job_id, status; paginated
  - `show(int $id)` — full application detail: student profile, resume download link, cover letter
  - `updateStatus(UpdateApplicationStatusRequest $request, int $id)` — change status; dispatch notification email

- `app/Http/Controllers/Admin/ApplicationController.php`
  - `index(Request $request)` — all applications; filter by job_id, student_id, status

### Form Requests
- `StoreApplicationRequest` — job_id (required, published job), resume_id (optional, must belong to student), cover_letter (optional, max 2000 chars)
- `UpdateApplicationStatusRequest` — status (reviewed|interviewed|accepted|rejected), notes (optional)

### Jobs / Mail
- `app/Jobs/SendApplicationReceivedJob.php` — notifies enterprise of new application; uses `email-templates/job-application-received.html`

### Services
- `app/Services/ApplicationService.php`
  - `apply(Student $student, array $data): Application` — validate job is published, student hasn't already applied, create row, dispatch notification

---

## Application Status Flow

```
pending → reviewed → interviewed → accepted
                  ↘              ↘ rejected
withdrawn (student can withdraw only from pending)
```

---

## Student Application List Response

Each item includes:
- application: id, status, applied_at, cover_letter
- job: id, title, location, job_type, salary_range
- enterprise: id, company_name, logo
- resume: id, file_name, file_type (if attached)

---

## Enterprise Application Detail Response

Includes:
- application: all fields including notes
- student: profile summary (name, nationality, education, bio)
- resume: file info + presigned download URL (1 hour TTL)
- talent_card: if visible

---

## Business Rules

- A student can only apply once per job (`UNIQUE KEY uk_job_student`)
- Student can only withdraw from `pending` status applications
- Enterprise can only view applications for their own jobs
- Application resume_id must belong to the applying student
- Job must be in `published` status to accept applications

---

## Acceptance Criteria

- [ ] `POST /api/student/applications` creates application, dispatches email to enterprise
- [ ] Duplicate application (same student + job) returns error
- [ ] Student can only apply to published jobs
- [ ] `DELETE /api/student/applications/{id}` withdraws pending application; blocked for non-pending
- [ ] `GET /api/enterprise/applications` returns only applications for own jobs
- [ ] `GET /api/enterprise/applications/{id}` returns student profile + resume download URL
- [ ] Enterprise cannot see applications for other enterprises' jobs
- [ ] `PUT /api/enterprise/applications/{id}/status` updates status and logs notes
- [ ] Admin can see all applications regardless of enterprise
- [ ] Paginations and filters work

---

## Notes

- Resume download URL in application detail: generate OSS presigned URL (TASK-016), not a direct OSS URL
- When enterprise changes status to `rejected`, dispatch notification to student (simple email)
- When enterprise changes status to `accepted`, dispatch notification to student
- Keep these email notifications simple (text) — proper HTML templates in TASK-017
