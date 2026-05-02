# TASK-011: Backend Enterprise Module

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-004, TASK-016  
**Priority:** HIGH  

---

## Objective

Implement enterprise profile management and job posting CRUD. Enterprise accounts must be verified (activated by admin) before they can post jobs or view resumes. Includes enterprise self-management and admin management of enterprise accounts.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.7 (Jobs)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.3, 1.2.10, 2.3.5
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.3 (Enterprise Account)

---

## API Endpoints to Implement

### Enterprise Profile
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/enterprise/profile` | Enterprise | Get own profile |
| PUT | `/api/enterprise/profile` | Enterprise | Update own profile |
| POST | `/api/enterprise/profile/logo` | Enterprise | Upload company logo |
| GET | `/api/enterprises` | None | Public list of verified enterprises |
| GET | `/api/enterprises/{id}` | None | Public enterprise profile |

### Jobs
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/jobs` | None | Public list of published jobs |
| GET | `/api/jobs/{id}` | None | Public job detail |
| GET | `/api/enterprise/jobs` | Enterprise | Own job postings (all statuses) |
| POST | `/api/enterprise/jobs` | Enterprise (verified) | Create job posting |
| PUT | `/api/enterprise/jobs/{id}` | Enterprise | Update own job |
| DELETE | `/api/enterprise/jobs/{id}` | Enterprise | Delete own job |
| PUT | `/api/enterprise/jobs/{id}/publish` | Enterprise (verified) | Publish job |
| PUT | `/api/enterprise/jobs/{id}/close` | Enterprise | Close job (stop applications) |
| GET | `/api/admin/jobs` | Admin | List all jobs |
| DELETE | `/api/admin/jobs/{id}` | Admin | Delete any job |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Enterprise/ProfileController.php`
  - `show()` — own enterprise profile
  - `update(UpdateEnterpriseProfileRequest $request)` — update company info
  - `uploadLogo(Request $request)` — upload logo to OSS
- `app/Http/Controllers/Public/EnterpriseController.php`
  - `index(Request $request)` — public list: verified=true only; filter by industry; paginated
  - `show(int $id)` — public enterprise detail with job count
- `app/Http/Controllers/Enterprise/JobController.php`
  - `index()` — own jobs all statuses
  - `store(StoreJobRequest $request)` — create; require enterprise_verified status
  - `update(UpdateJobRequest $request, int $id)` — update own job
  - `destroy(int $id)` — delete own job
  - `publish(int $id)` — publish job
  - `close(int $id)` — close job
- `app/Http/Controllers/Public/JobController.php`
  - `index(Request $request)` — published + not-expired jobs; filters: location, job_type, salary range; paginated
  - `show(int $id)` — job detail with enterprise info; increment view_count
- `app/Http/Controllers/Admin/JobController.php`
  - `index(Request $request)` — all jobs with all statuses
  - `destroy(int $id)` — delete any job

### Middleware
- `app/Http/Middleware/RequireEnterpriseVerified.php` — check `enterprise_status = enterprise_verified`; return 403 with `ENTERPRISE_NOT_VERIFIED` if not

### Form Requests
- `UpdateEnterpriseProfileRequest` — company_name, industry, scale, description, website, address, contact_name, contact_phone, prefer_lang
- `StoreJobRequest` / `UpdateJobRequest` — title (required), description (required), requirements, location, salary_min, salary_max, salary_currency, job_type (enum), expires_at

---

## Job Publishing Rules

- `status` flow: `draft → published → closed|expired`
- Only `enterprise_verified` enterprises can publish jobs
- `expires_at` — when past, job auto-expires (set by cron job or on read: if `expires_at < now()` mark as expired)
- Deleted jobs: remove from public listing; existing applications remain in DB

---

## Public Job List Filters

`GET /api/jobs` query params:
- `?location=Beijing`
- `?job_type=full_time|part_time|contract|internship`
- `?salary_min=5000`
- `?salary_max=20000`
- `?search=keyword` (title/description)
- `?enterprise_id=1`
- `?page=1&per_page=20`

---

## Public Enterprise List Filters

`GET /api/enterprises` query params:
- `?industry=Technology`
- `?search=company_name`

---

## Acceptance Criteria

- [ ] `GET /api/enterprise/profile` returns own enterprise profile (enterprise auth)
- [ ] `PUT /api/enterprise/profile` updates company info
- [ ] `POST /api/enterprise/profile/logo` uploads logo to OSS
- [ ] `GET /api/enterprises` returns only verified=true enterprises
- [ ] `GET /api/jobs` returns only published, non-expired jobs
- [ ] `POST /api/enterprise/jobs` blocked for unverified enterprise (returns ENTERPRISE_NOT_VERIFIED)
- [ ] `PUT /api/enterprise/jobs/{id}/publish` blocked for unverified enterprise
- [ ] Job view_count increments on public `GET /api/jobs/{id}`
- [ ] Enterprise can only edit/delete own jobs
- [ ] Admin can delete any job
- [ ] Job expiry: expired jobs not returned in public list
- [ ] Public enterprise list shows job_count in response

---

## Notes

- `enterprises.verified` (boolean) is set to true after admin runs `activate-enterprise` from TASK-006
- The `enterprise_verified` check should use the `users.enterprise_status` field, not `enterprises.verified` — ensure both are kept in sync when admin activates
- Enterprise logo upload: accept image/*, max 2MB, resize to max 400x400 px, upload to OSS
- Jobs with `expires_at` in the past should return status=expired — either set by a scheduled command or computed on read
