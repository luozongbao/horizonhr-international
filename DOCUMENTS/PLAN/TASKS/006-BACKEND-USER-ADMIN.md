# TASK-006: Backend User & Admin Management Module

**Phase:** 3 — Backend: Administration & Content  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** HIGH  

---

## Objective

Implement admin-facing user management endpoints: list/filter users, view user details, activate/suspend accounts, activate enterprise accounts, and admin account self-management. Also implement admin creation (admin-only endpoint to add new admin accounts).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.1 (Users)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.1–1.2.4, 2.3.4
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.1 (Admin Account — User Management)

---

## API Endpoints to Implement

All require `auth:sanctum` + `role:admin` middleware:

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/users` | List all users with filters + pagination |
| GET | `/api/users/{id}` | Get user detail with role profile |
| PUT | `/api/users/{id}/status` | Activate / suspend / delete user |
| PUT | `/api/users/{id}/activate-enterprise` | Approve enterprise account |
| POST | `/api/users/admin` | Create new admin account |
| DELETE | `/api/users/{id}` | Soft-delete user (status=deleted) |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Admin/UserController.php`
  - `index(Request $request)` — paginated list; filters: `role`, `status`, `search` (name/email), `nationality`, `enterprise_status`; sort by `created_at` desc
  - `show(int $id)` — user + full role profile (student/enterprise/admin with related data counts)
  - `updateStatus(UpdateUserStatusRequest $request, int $id)` — change status; validate allowed transitions
  - `activateEnterprise(int $id)` — set enterprise_status=enterprise_verified, status=active; dispatch activation email
  - `createAdmin(CreateAdminRequest $request)` — create user (role=admin, status=active) + admins row; send welcome email
  - `destroy(int $id)` — soft delete (set status=deleted); cannot delete self

### Form Requests
- `app/Http/Requests/Admin/UpdateUserStatusRequest.php`
  - status: enum(active, suspended, deleted)
  - reason: optional string
- `app/Http/Requests/Admin/CreateAdminRequest.php`
  - email, password (or auto-generate), name, phone (optional)

### Jobs / Mail
- `app/Jobs/SendEnterpriseActivatedJob.php` — dispatched when enterprise account activated; uses `email-templates/enterprise-activated.html`
- `app/Jobs/SendEnterprisePendingNotifyAdminJob.php` — dispatched when enterprise registers; uses `email-templates/enterprise-pending-notify-admin.html`

### Routes
```php
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index']);
    Route::post('/admin', [UserController::class, 'createAdmin']);
    Route::get('/{id}', [UserController::class, 'show']);
    Route::put('/{id}/status', [UserController::class, 'updateStatus']);
    Route::put('/{id}/activate-enterprise', [UserController::class, 'activateEnterprise']);
    Route::delete('/{id}', [UserController::class, 'destroy']);
});
```

---

## User Detail Response Shape

```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "student@example.com",
    "role": "student",
    "status": "active",
    "email_verified": true,
    "prefer_lang": "en",
    "last_login_at": "2026-05-01T10:00:00Z",
    "created_at": "2026-04-01T00:00:00Z",
    "profile": {
      "name": "Somchai Smith",
      "nationality": "Thailand",
      "phone": "+66...",
      "verified": false
    },
    "stats": {
      "resumes_count": 2,
      "applications_count": 3,
      "interviews_count": 1
    }
  }
}
```

---

## User List Response (Paginated)

Include `meta` with pagination. Each item in `data` array has: id, email, role, status, email_verified, created_at, and summary of profile (name/company_name).

Filters via query params:
- `?role=student|enterprise|admin`
- `?status=pending|active|suspended|deleted`
- `?search=keyword` (matches email or name/company_name)
- `?enterprise_status=pending|enterprise_verified`
- `?page=1&per_page=20`

---

## Business Rules

- Admin cannot change their own status or delete themselves
- Enterprise `activate-enterprise` endpoint: only call if current enterprise_status=pending
- When suspending a user, revoke all their active Sanctum tokens
- Status transition rules:
  - `pending → active` ✅
  - `active → suspended` ✅
  - `suspended → active` ✅
  - `active/suspended → deleted` ✅ (soft delete)
  - `deleted → any` ❌ (cannot reactivate deleted)

---

## Acceptance Criteria

- [ ] `GET /api/users` returns paginated user list (admin only)
- [ ] Role filter, status filter, and search work correctly
- [ ] `GET /api/users/{id}` returns user with profile and stats
- [ ] `PUT /api/users/{id}/status` changes status and revokes tokens on suspend
- [ ] `PUT /api/users/{id}/activate-enterprise` activates enterprise, sends email
- [ ] Enterprise activation email job dispatched on new enterprise registration (hook into register endpoint from TASK-004)
- [ ] `POST /api/users/admin` creates admin account
- [ ] Admin cannot delete/suspend themselves
- [ ] Non-admin requests return 403
- [ ] Deleted users cannot be restored
- [ ] Pagination metadata included in list response

---

## Notes

- The enterprise pending notification email should also be hooked into TASK-004's enterprise registration — add a `SendEnterprisePendingNotifyAdminJob` dispatch in `AuthService::createEnterprise()`
- "Delete" is always soft-delete (status=deleted) — no hard deletes
- Admin accounts created via `POST /api/users/admin` start with status=active and email_verified=true (no confirmation needed)
