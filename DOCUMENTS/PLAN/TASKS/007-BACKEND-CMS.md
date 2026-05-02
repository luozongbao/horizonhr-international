# TASK-007: Backend CMS Module (Pages + Posts)

**Phase:** 3 — Backend: Administration & Content  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** MEDIUM  

---

## Objective

Implement the CMS (Content Management System) for managing static website pages and news/announcement posts. All content fields are multi-language (zh_cn, en, th). Public endpoints allow unauthenticated visitors to read published content; admin-only endpoints manage create/update/delete.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Sections 3.4 (Pages), 3.5 (Posts)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.6–1.2.7 (pages, posts tables), 2.3.3
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C (CMS for Website)

---

## API Endpoints to Implement

### Pages
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/pages` | None | List published pages |
| GET | `/api/pages/{slug}` | None | Get page by slug |
| POST | `/api/pages` | Admin | Create page |
| PUT | `/api/pages/{id}` | Admin | Update page |
| DELETE | `/api/pages/{id}` | Admin | Delete page |

### Posts (News/Announcements)
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/posts` | None | List published posts, with filters |
| GET | `/api/posts/{id}` | None | Get post detail (increments view_count) |
| POST | `/api/posts` | Admin | Create post |
| PUT | `/api/posts/{id}` | Admin | Update post |
| DELETE | `/api/posts/{id}` | Admin | Delete post |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Public/PageController.php`
  - `index()` — return all published pages (id, slug, title by lang, order_num)
  - `show(string $slug)` — return full page by slug; 404 if draft or not found
- `app/Http/Controllers/Admin/PageController.php`
  - `store(StorePageRequest $request)` — create page
  - `update(UpdatePageRequest $request, int $id)` — update page
  - `destroy(int $id)` — delete page (check no posts reference it)
  - `index()` — list ALL pages including drafts (admin view)
  - `show(int $id)` — get page by ID (admin view, includes drafts)

- `app/Http/Controllers/Public/PostController.php`
  - `index(Request $request)` — published posts only; filter by `category`, `page_id`, `search`; paginated; sort by `published_at` desc
  - `show(int $id)` — return post, increment view_count; 404 if draft
- `app/Http/Controllers/Admin/PostController.php`
  - `index()` — all posts including drafts; same filters + status filter
  - `store(StorePostRequest $request)`
  - `update(UpdatePostRequest $request, int $id)`
  - `destroy(int $id)`
  - `publish(int $id)` — set status=published, published_at=now()
  - `unpublish(int $id)` — set status=draft

### Form Requests
- `StorePageRequest` / `UpdatePageRequest` — validate title_zh_cn, title_en, title_th (required), content_* (nullable), slug (unique), status, type, order_num
- `StorePostRequest` / `UpdatePostRequest` — validate title_*, content_*, category (enum), thumbnail (nullable), status, page_id (nullable FK)

### Routes
```php
// Public
Route::get('/pages', [PublicPageController::class, 'index']);
Route::get('/pages/{slug}', [PublicPageController::class, 'show']);
Route::get('/posts', [PublicPostController::class, 'index']);
Route::get('/posts/{id}', [PublicPostController::class, 'show']);

// Admin
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('/admin/pages', AdminPageController::class);
    Route::apiResource('/admin/posts', AdminPostController::class);
    Route::post('/admin/posts/{id}/publish', [AdminPostController::class, 'publish']);
    Route::post('/admin/posts/{id}/unpublish', [AdminPostController::class, 'unpublish']);
});
```

---

## Content Field Pattern

All CMS content follows the multi-language pattern from `DESIGN_SYSTEM.md`:
- `title_zh_cn`, `title_en`, `title_th`
- `content_zh_cn`, `content_en`, `content_th`
- `meta_title_zh_cn`, `meta_title_en`, `meta_title_th`
- `meta_desc_zh_cn`, `meta_desc_en`, `meta_desc_th`

The API returns all language variants (admin). Public endpoints return all variants — the frontend picks the correct language.

---

## Post Categories (ENUM)

Per SYSTEM_DESIGN.md `posts` table:
- `company_news` — Company news
- `industry_news` — Industry news
- `study_abroad` — Study abroad policies
- `recruitment` — Enterprise recruitment info

---

## Page Types (ENUM)

- `page` — Static page (About, Study in China, etc.)
- `announcement` — Announcement/notice

---

## Admin List Response (Posts)

Include all fields including status, view_count, published_at, created_at. Support pagination and filters:
- `?status=draft|published|archived`
- `?category=company_news|...`
- `?search=keyword`
- `?page=1&per_page=20`

---

## Acceptance Criteria

- [ ] `GET /api/pages` returns only published pages
- [ ] `GET /api/pages/{slug}` returns published page with all language fields
- [ ] `POST /api/pages` (admin) creates page with all required fields
- [ ] `PUT /api/pages/{id}` (admin) updates page, slug uniqueness validated
- [ ] `DELETE /api/pages/{id}` (admin) deletes page
- [ ] `GET /api/posts` returns paginated published posts, newest first
- [ ] Category filter works correctly
- [ ] `GET /api/posts/{id}` increments view_count on each call
- [ ] `POST /api/posts/{id}/publish` sets status=published and published_at
- [ ] Draft posts not returned in public endpoints
- [ ] Non-admin create/update/delete returns 403
- [ ] All language fields (zh_cn, en, th) stored and returned correctly

---

## Notes

- Slug must be URL-safe: lowercase, alphanumeric, hyphens only. Auto-generate from title_en if not provided.
- `view_count` increment should be done directly in DB (`increment('view_count')`) without loading the model to avoid race conditions
- Content fields support HTML (rich text) — no server-side sanitization for admin inputs, but document this in code comments
- The `pages` endpoint for public does NOT need pagination (there are few static pages) — return all as array
