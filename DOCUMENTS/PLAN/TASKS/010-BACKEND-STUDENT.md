# TASK-010: Backend Student Module

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-004, TASK-016  
**Priority:** HIGH  

---

## Objective

Implement all student-facing and admin-facing APIs for student profile management, resume upload/management with visibility controls, and talent card generation. Resume files are stored on OSS (TASK-016 must be complete, or stub with local storage).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.6 (Resumes)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.2, 1.2.8, 1.2.9, 2.3.4
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.2 (Student Account), IV.C.1 (Resume Upload)

---

## API Endpoints to Implement

### Student Profile
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/student/profile` | Student | Get own profile |
| PUT | `/api/student/profile` | Student | Update own profile |
| POST | `/api/student/profile/avatar` | Student | Upload avatar |

### Resumes
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/student/resumes` | Student | Get own resumes |
| POST | `/api/student/resumes` | Student | Upload resume file |
| PUT | `/api/student/resumes/{id}` | Student | Update resume visibility |
| DELETE | `/api/student/resumes/{id}` | Student | Delete own resume |
| GET | `/api/admin/resumes` | Admin | List all resumes with filters |
| PUT | `/api/admin/resumes/{id}/review` | Admin | Approve / reject resume |

### Talent Cards
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/student/talent-card` | Student | Get own talent card |
| PUT | `/api/student/talent-card` | Student | Update talent card |
| GET | `/api/talent` | None (public) | List public talent cards |
| GET | `/api/talent/{id}` | Optional | Get talent card detail |
| GET | `/api/enterprise/talent` | Enterprise | List talent with enterprise-visible resumes |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Student/ProfileController.php`
  - `show()`, `update(UpdateStudentProfileRequest $request)`, `uploadAvatar(Request $request)`
- `app/Http/Controllers/Student/ResumeController.php`
  - `index()` — own resumes
  - `store(StoreResumeRequest $request)` — upload file to OSS, create resume row
  - `update(UpdateResumeRequest $request, int $id)` — update visibility only
  - `destroy(int $id)` — delete file from OSS + DB row
- `app/Http/Controllers/Admin/ResumeController.php`
  - `index(Request $request)` — list with filters: status, visibility, nationality, student_name
  - `review(ReviewResumeRequest $request, int $id)` — approve/reject; on approve, trigger talent card generation
- `app/Http/Controllers/Public/TalentController.php`
  - `index(Request $request)` — public talent cards (status=visible or featured), filterable
  - `show(int $id)` — public talent card detail
- `app/Http/Controllers/Student/TalentCardController.php`
  - `show()` — own talent card
  - `update(UpdateTalentCardRequest $request)` — update talent card content
- `app/Http/Controllers/Enterprise/TalentController.php`
  - `index(Request $request)` — talent cards where student has enterprise-visible resumes

### Services
- `app/Services/ResumeService.php`
  - `uploadResume(Student $student, UploadedFile $file): Resume` — validate type/size, upload to OSS, create DB row
  - `deleteResume(Resume $resume): void` — delete from OSS + DB
  - `generateTalentCard(Student $student): TalentCard` — auto-populate talent card from student profile + latest resume data
- `app/Services/OssService.php` — (implemented in TASK-016; stub here if needed)

### Form Requests
- `UpdateStudentProfileRequest` — name, nationality, phone, birth_date, gender, address, bio, prefer_lang
- `StoreResumeRequest` — file (max 20MB, types: pdf,doc,docx,jpg,jpeg,png), visibility
- `UpdateResumeRequest` — visibility only: admin_only|enterprise_visible|public
- `ReviewResumeRequest` — status: approved|rejected, notes (optional)
- `UpdateTalentCardRequest` — display_name, major, education, university, languages (JSON), skills (JSON), work_experience (JSON), job_intention, status

---

## Resume Upload Requirements

Per REQ IV.C.1:
- Accepted formats: PDF, Word (doc/docx), Images (jpg/png)
- Maximum size: 20MB
- Visibility options: `admin_only` | `enterprise_visible` | `public`
- Initial status after upload: `pending` (awaiting admin review)
- After admin approves: auto-generate/update talent card

---

## Public Talent Pool Filters

`GET /api/talent` query params:
- `?nationality=Thailand`
- `?major=Computer+Science`
- `?education=bachelor`
- `?language=en`
- `?status=visible` (default) or `featured`
- `?page=1&per_page=20`

---

## Admin Resume List Filters

`GET /api/admin/resumes` query params:
- `?status=pending|approved|rejected`
- `?visibility=admin_only|enterprise_visible|public`
- `?nationality=Thailand`
- `?search=student_name`
- `?page=1&per_page=20`

Admin response includes: resume info + student profile summary (name, nationality, university).

---

## Talent Card Visibility

- `hidden` — not visible to anyone except owner and admin
- `visible` — visible in public talent pool
- `featured` — highlighted in public talent pool

The talent card is only shown publicly if at least one resume is `approved`.

---

## Acceptance Criteria

- [ ] `GET /api/student/profile` returns own student profile (student auth only)
- [ ] `PUT /api/student/profile` updates profile fields
- [ ] `POST /api/student/resumes` uploads file to OSS/local storage, creates resume row
- [ ] File type validation (pdf, doc, docx, jpg, png only)
- [ ] File size validation (max 20MB)
- [ ] `PUT /api/student/resumes/{id}/visibility` updates visibility
- [ ] `DELETE /api/student/resumes/{id}` deletes file and DB row
- [ ] `GET /api/admin/resumes` returns all resumes with pagination (admin only)
- [ ] `PUT /api/admin/resumes/{id}/review` with status=approved triggers talent card creation
- [ ] `GET /api/talent` returns public talent cards (no auth required)
- [ ] Nationality, major, education filters work
- [ ] Enterprise can see talent pool with enterprise-visible resumes
- [ ] Student cannot see other students' resumes via API

---

## Notes

- Avatar upload: accept image/*, max 2MB, resize to max 500x500, upload to OSS
- The `resumes` endpoint for enterprise portal should only return talent cards where student has at least one `enterprise_visible` or `public` approved resume
- Resume file download: return OSS presigned URL with 1-hour expiry, NOT a public permanent URL
- PDPA: only show resume data to enterprises if student's visibility setting allows it
