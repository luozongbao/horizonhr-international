# TASK-037: Frontend Admin CMS (Pages & News)

**Phase:** 11 — Frontend: Admin Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-007  
**Priority:** MEDIUM  

---

## Objective

Implement the admin CMS management pages: static page editor (About, Study in China, etc.) and news/posts management (CRUD with thumbnail upload).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-news.html` — News management mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.7 (CMS Pages), Section 3.8 (Posts/News)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.7 (Admin: CMS)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/admin/pages` | List CMS pages |
| GET | `/api/admin/pages/{id}` | Page detail |
| PUT | `/api/admin/pages/{id}` | Update page content |
| GET | `/api/admin/posts` | List news/posts |
| POST | `/api/admin/posts` | Create post |
| PUT | `/api/admin/posts/{id}` | Update post |
| DELETE | `/api/admin/posts/{id}` | Delete post |
| PUT | `/api/admin/posts/{id}/publish` | Publish/unpublish |

---

## Deliverables

### Admin Pages (Static CMS Pages)
**`frontend/src/views/admin/PagesPage.vue`**

Layout:
1. **Pages List** — table: page name, slug, last updated, "Edit" button
   - Fixed pages: Home, About, Study in China, Corporate, Contact
   - Cannot create or delete pages (fixed set)
2. **Page Editor (`el-dialog`)**:
   - Title (read-only, shows slug)
   - Content (multi-language tabs: EN/ZH_CN/TH)
   - For Home page: additional JSON fields editor (banners array, stats numbers)
   - Rich text editor using `@vueup/vue-quill` or a simple `el-input type="textarea"`
   - Save → `PUT /api/admin/pages/{id}`

### Admin News/Posts
**`frontend/src/views/admin/NewsPage.vue`**

Layout (matches `admin-news.html`):
1. **"Create Post" button**
2. **Status Tabs**: All / Published / Draft
3. **Posts Table**:
   - Columns: Thumbnail, Title, Category, Author, Published Date, Status, Actions
   - Actions: "Edit", "Toggle Publish", "Delete"
4. **Create/Edit Post Form (`el-dialog` or full page)**:
   - Title (multi-language tabs: EN/ZH_CN/TH)
   - Slug (auto-generated from EN title, editable)
   - Category (text input or select)
   - Thumbnail (upload, store to OSS)
   - Content (multi-language, rich text)
   - Published At (datetime picker, optional — if blank = now on publish)
   - Save as Draft / Publish

**Rich Text Editor:**
- Use `@vueup/vue-quill`: `npm install @vueup/vue-quill`
- Toolbar: headers, bold, italic, underline, lists, links, images
- Image upload in editor: trigger file picker, upload to OSS via `/api/admin/media/upload` (simple endpoint), insert returned URL into editor

**Slug generation:**
```js
const generateSlug = (title) => title.toLowerCase()
  .replace(/[^a-z0-9\s-]/g, '')
  .replace(/\s+/g, '-')
  .replace(/-+/g, '-')
```

---

## API Module Additions (to `admin.js`)
```js
export const adminApi = {
  // CMS Pages
  getPages(),
  getPage(id),
  updatePage(id, data),
  
  // Posts
  getPosts(params),
  createPost(data),
  updatePost(id, data),
  deletePost(id),
  publishPost(id),
  unpublishPost(id),
  uploadMedia(formData),  // for rich text image uploads
}
```

---

## i18n Keys to Add

```json
"adminCms": {
  "pages": {
    "pageTitle": "CMS Pages",
    "editPage": "Edit Page",
    "lastUpdated": "Last Updated"
  },
  "news": {
    "pageTitle": "News & Posts",
    "createPost": "Create Post",
    "editPost": "Edit Post",
    "slug": "URL Slug",
    "category": "Category",
    "thumbnail": "Thumbnail",
    "content": "Content",
    "saveAsDraft": "Save as Draft",
    "publish": "Publish",
    "unpublish": "Unpublish",
    "deleteConfirm": "Delete this post? This cannot be undone."
  }
}
```

---

## Acceptance Criteria

- [ ] CMS pages list shows all fixed pages
- [ ] Page editor opens with existing content pre-filled
- [ ] Multi-language tabs show content per locale
- [ ] Page content saves via API
- [ ] Posts list shows all posts with status filter
- [ ] "Create Post" opens form dialog
- [ ] Slug auto-generates from EN title
- [ ] Thumbnail upload works and previews
- [ ] Rich text editor (Quill) renders and allows editing
- [ ] Publish/unpublish toggles post status
- [ ] Delete shows confirmation dialog
- [ ] All text via i18n

---

## Notes

- Rich text `@vueup/vue-quill`: `npm install @vueup/vue-quill`
- For post content multi-language: use `el-tabs` with separate Quill editor per language tab
- Image upload in Quill: custom image handler — intercept Quill's image insert, trigger hidden `<input type="file">`, upload to backend, insert returned URL
- Home page JSON editor (banners etc.) can be a simple `<el-input type="textarea">` with JSON validation, or skip for MVP (banners managed via plain text fields)
