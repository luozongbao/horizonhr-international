# TASK-009: Backend Language & Translations Module

**Phase:** 3 — Backend: Administration & Content  
**Status:** Pending  
**Depends On:** TASK-008  
**Priority:** MEDIUM  

---

## Objective

Implement two separate API modules:
1. **Language Settings** (`/api/languages`) — manage which languages are active on the site (from `language_settings` table)
2. **Translations** (`/api/translations`) — manage translation key-value pairs (from `languages` table) with export/import

These two are intentionally separate APIs serving different purposes.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Sections 3.2 (Language Settings) and 3.3 (Translations)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.5 (languages table), 1.2.4.1 (language_settings), 2.3.9
3. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Frontend integration pattern for language settings
4. `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` — Admin UI reference

---

## API Endpoints to Implement

### Language Settings (`language_settings` table)
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/languages` | None | Get all active languages |
| GET | `/api/admin/languages` | Admin | Get all languages (active + inactive) |
| POST | `/api/admin/languages` | Admin | Add a new language |
| PUT | `/api/admin/languages/{code}` | Admin | Update language (name, flag, active, position) |
| DELETE | `/api/admin/languages/{code}` | Admin | Remove language (cannot remove default `en`) |

### Translations (`languages` table — translation keys)
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/translations` | None | Get all translation keys as flat object |
| GET | `/api/admin/translations` | Admin | List all translation keys with pagination |
| GET | `/api/admin/translations/{key}` | Admin | Get single translation key |
| POST | `/api/admin/translations` | Admin | Create translation key |
| PUT | `/api/admin/translations/{key}` | Admin | Update translation values |
| DELETE | `/api/admin/translations/{key}` | Admin | Delete translation key |
| GET | `/api/admin/translations/export` | Admin | Export all as JSON file |
| POST | `/api/admin/translations/import` | Admin | Import from JSON file |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Public/LanguageController.php`
  - `activeLanguages()` — return only `is_active=true` language settings rows, ordered by position

- `app/Http/Controllers/Admin/LanguageController.php`
  - `index()` — return ALL language settings (active + inactive)
  - `store(StoreLanguageRequest $request)` — add new language
  - `update(UpdateLanguageRequest $request, string $code)` — update language
  - `destroy(string $code)` — delete; block if `code = 'en'` (default) with `CANNOT_DELETE_DEFAULT` error

- `app/Http/Controllers/Public/TranslationController.php`
  - `index(Request $request)` — return all translations as flat `{ key: { en: '...', zh_cn: '...', th: '...' } }` object; optionally filter by `?lang=en` to return flat `{ key: value }`

- `app/Http/Controllers/Admin/TranslationController.php`
  - `index()` — paginated list of all keys with all language values
  - `show(string $key)`
  - `store(StoreTranslationRequest $request)`
  - `update(UpdateTranslationRequest $request, string $key)`
  - `destroy(string $key)`
  - `export()` — return JSON file download (Content-Disposition: attachment)
  - `import(Request $request)` — accept JSON file, upsert all keys

### Form Requests
- `StoreLanguageRequest` — code (required, unique, max 10), name, native_name, flag (emoji), is_active, position
- `UpdateLanguageRequest` — name, native_name, flag, is_active, position (all optional)
- `StoreTranslationRequest` — key (required, unique, max 100), zh_cn, en, th
- `UpdateTranslationRequest` — zh_cn, en, th (all optional)

### Caching
- Cache active languages with key `languages:active`, TTL 1 hour
- Clear cache after any language settings change

### Routes
```php
// Public
Route::get('/languages', [PublicLanguageController::class, 'activeLanguages']);
Route::get('/translations', [PublicTranslationController::class, 'index']);

// Admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/languages', [AdminLanguageController::class, 'index']);
    Route::post('/admin/languages', [AdminLanguageController::class, 'store']);
    Route::put('/admin/languages/{code}', [AdminLanguageController::class, 'update']);
    Route::delete('/admin/languages/{code}', [AdminLanguageController::class, 'destroy']);

    Route::get('/admin/translations/export', [AdminTranslationController::class, 'export']);
    Route::post('/admin/translations/import', [AdminTranslationController::class, 'import']);
    Route::get('/admin/translations', [AdminTranslationController::class, 'index']);
    Route::post('/admin/translations', [AdminTranslationController::class, 'store']);
    Route::get('/admin/translations/{key}', [AdminTranslationController::class, 'show']);
    Route::put('/admin/translations/{key}', [AdminTranslationController::class, 'update']);
    Route::delete('/admin/translations/{key}', [AdminTranslationController::class, 'destroy']);
});
```

---

## Public Language Response Shape

```json
{
  "success": true,
  "data": [
    { "code": "en", "name": "English", "native_name": "English", "flag": "🇬🇧", "position": 1 },
    { "code": "zh_cn", "name": "中文简体", "native_name": "简体中文", "flag": "🇨🇳", "position": 2 },
    { "code": "th", "name": "ภาษาไทย", "native_name": "ภาษาไทย", "flag": "🇹🇭", "position": 3 }
  ]
}
```

## Public Translations Response Shape

When called as `GET /api/translations`:
```json
{
  "success": true,
  "data": {
    "website_name": { "en": "HorizonHR International...", "zh_cn": "湖北豪睿...", "th": "..." },
    "home_banner_title": { "en": "...", "zh_cn": "...", "th": "..." },
    ...
  }
}
```

When called as `GET /api/translations?lang=en`:
```json
{
  "success": true,
  "data": {
    "website_name": "HorizonHR International...",
    "home_banner_title": "Connect Southeast Asian...",
    ...
  }
}
```

---

## Export/Import Format

Export JSON structure:
```json
{
  "version": "1.0",
  "exported_at": "2026-05-02T00:00:00Z",
  "translations": {
    "website_name": { "en": "...", "zh_cn": "...", "th": "..." },
    ...
  }
}
```

Import: validate JSON structure, upsert each key (create if not exists, update if exists).

---

## Acceptance Criteria

- [ ] `GET /api/languages` returns only active languages, ordered by position
- [ ] `GET /api/admin/languages` returns all languages including inactive (admin)
- [ ] `DELETE /api/admin/languages/en` returns `CANNOT_DELETE_DEFAULT` error
- [ ] `PUT /api/admin/languages/{code}` updates language fields
- [ ] Language cache cleared after update
- [ ] `GET /api/translations` returns all keys with all language values
- [ ] `GET /api/translations?lang=en` returns flat key-value for English only
- [ ] `GET /api/admin/translations/export` returns downloadable JSON file
- [ ] `POST /api/admin/translations/import` imports keys and upserts correctly
- [ ] Pagination works on admin translations list
- [ ] New language code is unique (duplicate code rejected)
- [ ] Translation key is unique (duplicate key rejected)

---

## Notes

- The `languages` table (translation keys) holds the actual translation strings; `language_settings` holds which languages are enabled on the site — these are distinct
- Frontend (TASK-021) fetches `GET /api/languages` at app startup to determine which language tabs to show in the settings form
- Frontend i18n (Vue i18n) uses `GET /api/translations?lang={code}` to load dynamic translation overrides from DB on top of the static compiled translations
