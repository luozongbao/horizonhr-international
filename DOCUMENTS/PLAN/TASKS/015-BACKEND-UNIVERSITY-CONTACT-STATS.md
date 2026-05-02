# TASK-015: Backend University, Contact & Statistics Modules

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** MEDIUM  

---

## Objective

Implement three smaller backend modules:
1. **Universities** — CRUD for partner universities (public read, admin write)
2. **Contact** — Public contact form submission with admin notification
3. **Admin Statistics** — Aggregated dashboard statistics for admin

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Sections 3.12 (Universities), 3.13 (Statistics), 3.14 (Contact)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.17 (universities), 1.2.22 (contacts), 2.3.10 (Contact), Traceability 3.1
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.3 (Study in China: Partner Universities), IV.A.8 (Contact Us), IV.B.1 (Admin: Data Statistics)

---

## API Endpoints to Implement

### Universities
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/universities` | None | List universities with filters |
| GET | `/api/universities/{id}` | None | University detail |
| POST | `/api/admin/universities` | Admin | Create university |
| PUT | `/api/admin/universities/{id}` | Admin | Update university |
| DELETE | `/api/admin/universities/{id}` | Admin | Delete university |

### Contact
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/contact` | None | Submit contact form |
| GET | `/api/admin/contacts` | Admin | List contact submissions |
| PUT | `/api/admin/contacts/{id}/status` | Admin | Mark read / replied |

### Admin Statistics
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/admin/stats` | Admin | Dashboard statistics |

---

## Deliverables

### Controllers

**Universities:**
- `app/Http/Controllers/Public/UniversityController.php`
  - `index(Request $request)` — list all universities; filter by `location_city`, `location_region`, `program_types` (JSON contains), `search` (name); paginated
  - `show(int $id)` — full university detail
- `app/Http/Controllers/Admin/UniversityController.php`
  - `store`, `update`, `destroy` — standard CRUD

**Contact:**
- `app/Http/Controllers/Public/ContactController.php`
  - `submit(ContactFormRequest $request)` — create contact record; dispatch admin notification; rate limit: 3 per IP per hour
- `app/Http/Controllers/Admin/ContactController.php`
  - `index(Request $request)` — list; filter by status (unread/read/replied); paginated
  - `updateStatus(int $id, Request $request)` — update status to read/replied

**Statistics:**
- `app/Http/Controllers/Admin/StatisticsController.php`
  - `index(Request $request)` — aggregate stats; `?period=7d|30d|90d|1y|all`

### Form Requests
- `StoreUniversityRequest` / `UpdateUniversityRequest` — name_zh_cn, name_en, name_th, logo, cover_image, location, website, description, majors (JSON), program_types (JSON)
- `ContactFormRequest` — name (required), email (required, valid email), phone (optional), subject (required), message (required, max 2000 chars)

### Jobs / Mail
- `app/Jobs/SendContactNotificationJob.php` — notify admin email of new contact submission

### Services
- `app/Services/StatisticsService.php`
  - `getStats(string $period): array`
  - Queries to compute:
    - `total_users` — count by role (student, enterprise, admin)
    - `new_users` — count in period
    - `total_resumes` — count, count by status
    - `new_resumes` — count in period
    - `total_interviews` — count by status
    - `new_interviews` — count in period
    - `total_seminars` — count by status
    - `total_jobs` — count by status
    - `new_jobs` — count in period
    - `total_applications` — count by status
    - `daily_trend` — array of `{ date, new_users, new_resumes, new_interviews }` for the period

---

## University Filters

`GET /api/universities`:
- `?location_city=Wuhan`
- `?location_region=Hubei`
- `?program_types=bachelor` (JSON array contains check)
- `?search=keyword` (matches name_en or name_zh_cn)

---

## Contact Form Security

- Rate limit: 3 submissions per IP per hour (Redis throttle)
- Store `ip_address` from request (use `$request->ip()` — nginx must pass real IP)
- Status: `unread` (default) → `read` → `replied`
- Admin notification email: send to `settings.contact_email`

---

## Statistics Response Shape

```json
{
  "success": true,
  "data": {
    "period": "30d",
    "users": {
      "total": 500,
      "students": 420,
      "enterprises": 75,
      "admins": 5,
      "new_in_period": 42
    },
    "resumes": {
      "total": 380,
      "pending": 50,
      "approved": 310,
      "rejected": 20,
      "new_in_period": 28
    },
    "interviews": {
      "total": 95,
      "scheduled": 12,
      "completed": 78,
      "cancelled": 5,
      "new_in_period": 8
    },
    "seminars": {
      "total": 24,
      "scheduled": 3,
      "live": 0,
      "ended": 21
    },
    "jobs": {
      "total": 145,
      "published": 80,
      "closed": 65,
      "new_in_period": 15
    },
    "applications": {
      "total": 560,
      "pending": 120,
      "accepted": 75
    },
    "daily_trend": [
      { "date": "2026-04-01", "new_users": 3, "new_resumes": 5, "new_interviews": 1 },
      ...
    ]
  }
}
```

---

## Acceptance Criteria

- [ ] `GET /api/universities` returns paginated list with filters working
- [ ] `GET /api/universities/{id}` returns full detail including majors JSON
- [ ] Admin can create/update/delete universities
- [ ] `POST /api/contact` creates contact record, dispatches admin notification
- [ ] Contact form rate limited (3 per IP per hour)
- [ ] IP address stored in contacts.ip_address
- [ ] `GET /api/admin/contacts` returns paginated contact list with status filter
- [ ] `GET /api/admin/stats` returns aggregated statistics
- [ ] Period filter (7d, 30d, 90d, 1y, all) works correctly for date-bounded queries
- [ ] `daily_trend` array has correct number of days for the period
- [ ] Non-admin requests to admin stats return 403
- [ ] Contact status update (unread→read→replied) works

---

## Notes

- Statistics queries can be slow for large datasets — cache results for 5 minutes in Redis
- `daily_trend` should generate a date series (even for days with 0 counts) for clean chart data
- University `majors` and `program_types` fields are JSON — use MySQL's JSON_CONTAINS for filtering
- Contact notifications: send to `Setting::get('contact_email')` if configured, else skip
