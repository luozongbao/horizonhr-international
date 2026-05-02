# TASK-008: Backend Settings Module

**Phase:** 3 — Backend: Administration & Content  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** MEDIUM  

---

## Objective

Implement the site settings API: read all settings (public-safe subset), admin CRUD for all settings, file upload for logo/favicon, and SMTP connection testing. Settings are stored in the `settings` table as key-value pairs and served via API to both frontend (for site config) and admin (for management).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.4 (Settings)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.5 (settings table), 2.3.8
3. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Multi-language field pattern for settings
4. `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` — Settings page design reference
5. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C (Settings)

---

## API Endpoints to Implement

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/settings` | None | Get public settings (subset) |
| GET | `/api/admin/settings` | Admin | Get ALL settings grouped |
| PUT | `/api/admin/settings` | Admin | Bulk update settings |
| POST | `/api/admin/settings/test-smtp` | Admin | Test SMTP connection |
| POST | `/api/admin/settings/upload-logo` | Admin | Upload site logo |
| POST | `/api/admin/settings/upload-favicon` | Admin | Upload site favicon |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Public/SettingsController.php`
  - `index()` — return safe public settings (site names, logo, favicon, social links, contact info; exclude smtp credentials, debug flags)
- `app/Http/Controllers/Admin/SettingsController.php`
  - `index()` — return ALL settings grouped by `group` field
  - `update(UpdateSettingsRequest $request)` — bulk update: receive `{ key: value }` map, update each
  - `testSmtp()` — test SMTP connection using current settings from DB; return success/failure
  - `uploadLogo(Request $request)` — upload logo image to OSS, update `settings` key `logo`
  - `uploadFavicon(Request $request)` — upload favicon to OSS, update `settings` key `favicon`

### Form Requests
- `app/Http/Requests/Admin/UpdateSettingsRequest.php` — validate `settings` is array; validate specific keys if needed (e.g., smtp_port is numeric)

### Services
- `app/Services/SettingsService.php`
  - `getPublicSettings(): array` — return safe subset as flat key-value
  - `getAllGrouped(): array` — return all settings grouped by `group`
  - `bulkUpdate(array $data): void` — upsert each key-value pair
  - `testSmtp(): array` — test connection with current SMTP settings; return `['success' => bool, 'message' => '...']`
  - `clearCache(): void` — clear settings cache after update

### Caching
- Cache `getAllGrouped()` and `getPublicSettings()` results in Redis with key `settings:all` and `settings:public`, TTL 1 hour
- `clearCache()` called after any update

### Routes
```php
// Public
Route::get('/settings', [PublicSettingsController::class, 'index']);

// Admin
Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin/settings')->group(function () {
    Route::get('/', [AdminSettingsController::class, 'index']);
    Route::put('/', [AdminSettingsController::class, 'update']);
    Route::post('/test-smtp', [AdminSettingsController::class, 'testSmtp']);
    Route::post('/upload-logo', [AdminSettingsController::class, 'uploadLogo']);
    Route::post('/upload-favicon', [AdminSettingsController::class, 'uploadFavicon']);
});
```

---

## Public Settings Response Shape

```json
{
  "success": true,
  "data": {
    "site_name": "HorizonHR",
    "site_name_zh_cn": "湖北豪睿国际人才服务有限公司",
    "site_name_en": "HorizonHR International Talent Service",
    "site_name_th": "บริการบุคลากรระหว่างประเทศ HorizonHR",
    "logo": "https://oss.example.com/assets/logo.png",
    "logo_secondary": "https://oss.example.com/assets/logo-white.png",
    "favicon": "https://oss.example.com/assets/favicon.ico",
    "default_language": "en",
    "contact_email": "contact@horizonhr.com",
    "contact_phone": "+86 27-...",
    "contact_address": "...",
    "copyright": "© 2026 Hubei Horizon...",
    "social_wechat": "...",
    "social_whatsapp": "...",
    "social_line": "...",
    "social_facebook": "...",
    "social_linkedin": "..."
  }
}
```

> SMTP credentials, debug flags, and system flags are NEVER in public response.

---

## Admin Settings Response Shape

Grouped by `group`:
```json
{
  "success": true,
  "data": {
    "website": { "site_name": "HorizonHR", ... },
    "seo": { "seo_title": "...", ... },
    "social": { "social_wechat": "...", ... },
    "smtp": { "smtp_host": "...", "smtp_port": "587", ... },
    "system": { "maintenance_mode": "false", ... }
  }
}
```

---

## SMTP Test Logic

```php
// Use current smtp_* settings from DB
// Attempt to connect to SMTP server
// Return success message or error details
// Use Swift_SmtpTransport or PHP Mailer check
// Do NOT send an actual email — just test connection
```

Return: `{ success: true, message: "SMTP connection successful" }` or `{ success: false, error: { code: "SMTP_CONNECTION_FAILED", message: "..." } }`

---

## File Upload (Logo/Favicon)

- Accept: `image/*` for logo; `.ico`, `.png` for favicon
- Max size: 2MB
- Upload to OSS via `OssService` (from TASK-016; for now use local disk if OSS not yet integrated)
- Update the corresponding setting key in DB
- Clear settings cache
- Return the new URL

---

## Settings Key Conventions

Multi-language site name follows pattern `{key}_{lang_code}`:
- `site_name_en`, `site_name_zh_cn`, `site_name_th`
- `seo_title_en`, `seo_title_zh_cn`, `seo_title_th`

These must be included in both public and admin endpoints.

---

## Acceptance Criteria

- [ ] `GET /api/settings` returns public settings without SMTP credentials
- [ ] `GET /api/admin/settings` returns all settings grouped (admin only)
- [ ] `PUT /api/admin/settings` bulk-updates settings and clears cache
- [ ] `POST /api/admin/settings/test-smtp` tests SMTP and returns success/failure
- [ ] `POST /api/admin/settings/upload-logo` uploads file and updates logo setting
- [ ] `POST /api/admin/settings/upload-favicon` uploads file and updates favicon setting
- [ ] Settings cached in Redis (verify cache hit on second call)
- [ ] Cache cleared after update
- [ ] SMTP credentials never appear in public settings endpoint
- [ ] Multi-language settings keys (site_name_en, etc.) returned correctly

---

## Notes

- `Setting::get($key, $default)` helper from TASK-003 is used throughout other modules for reading settings
- Dynamic SMTP config: after settings update, the mail config must be updated at runtime — use `Config::set('mail.*', ...)` in a service provider or middleware that reads from DB settings on each request if `smtp_driver` is 'db'
- Logo and favicon paths stored as OSS URLs, not local paths, in production
