# TASK-024: Frontend About & Study in China Pages

**Phase:** 8 — Frontend: Public Pages  
**Status:** Pending  
**Depends On:** TASK-021, TASK-007  
**Priority:** MEDIUM  

---

## Objective

Implement the About Us page and Study in China page. Both pages display CMS-driven content with rich text, images, and university listings. The Study in China page additionally shows the partner university directory with search and filter.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/` — No specific mockup files; reference `index.html` for header/footer style consistency
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.7 (CMS Pages), Section 3.12 (Universities)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.2 (About Us), IV.A.3 (Study in China)
4. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Typography, colors

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/pages/about` | About Us CMS content |
| GET | `/api/pages/study-in-china` | Study in China CMS content |
| GET | `/api/universities` | List universities with filters |
| GET | `/api/universities/{id}` | University detail |

---

## Deliverables

### About Us Page
**`frontend/src/views/public/AboutPage.vue`**

Sections:
1. **Page Hero** — Banner with page title "About Us", breadcrumb
2. **Mission & Vision** — Two-column layout: mission statement + vision statement from CMS
3. **Our Story** — Rich text content from CMS `pages.about` content field
4. **Team Section** (optional) — If CMS includes team member JSON data
5. **Partners / Logos** — Partner logos strip (from university list or CMS)

Content rendering:
- Use `v-html` for rich text content (sanitize with DOMPurify to prevent XSS)
- Images from CMS use absolute OSS URLs

### Study in China Page
**`frontend/src/views/public/StudyInChinaPage.vue`**

Sections:
1. **Page Hero** — Banner with title "Study in China", breadcrumb
2. **Introduction** — Rich text from CMS `pages.study-in-china`
3. **Why Study in China** — Feature cards (content from CMS JSON or hardcoded): lower tuition, rich culture, HSK support, career opportunities
4. **University Directory**
   - Search bar: search by university name
   - Filters: location/region (dropdown), program type (Bachelor/Master/PhD)
   - University cards grid (3 columns desktop, 1 mobile):
     - Card: logo, name, location, programs offered, "View Details" button
   - Pagination (Element Plus `el-pagination`)
5. **University Detail Modal** (or page)
   - Full university info: name, logo, cover image, location, description, majors (formatted list), website link
   - Use `el-dialog` on desktop; navigate to `/study-in-china/{id}` on mobile

### University Detail Route (Optional Sub-route)
- `/study-in-china/:id` → `views/public/UniversityDetailPage.vue`
- Show full university details with majors list, gallery, links

---

## i18n Keys to Add

```json
"about": {
  "pageTitle": "About Us",
  "mission": "Our Mission",
  "vision": "Our Vision"
},
"studyInChina": {
  "pageTitle": "Study in China",
  "searchPlaceholder": "Search universities...",
  "filterRegion": "Region",
  "filterProgram": "Program Type",
  "programs": {
    "bachelor": "Bachelor's",
    "master": "Master's",
    "phd": "PhD",
    "language": "Language Program"
  },
  "viewDetails": "View Details",
  "website": "Visit Website",
  "location": "Location"
}
```

---

## Acceptance Criteria

- [ ] About page fetches and renders CMS content via `GET /api/pages/about`
- [ ] Study in China page fetches and renders CMS content
- [ ] University list loads from API with correct cards
- [ ] Search filters universities by name (client-side or via API `?search=`)
- [ ] Region and program type dropdowns filter university list
- [ ] University detail modal/page shows full information
- [ ] Pagination works for university list
- [ ] Rich text content rendered safely (DOMPurify sanitization)
- [ ] Pages render correctly when CMS returns empty content (no errors)
- [ ] Responsive layout on mobile/tablet/desktop

---

## Notes

- `v-html` + DOMPurify: install `dompurify` package; create a `useSanitize` composable
- CMS `pages` are identified by `slug` field: `about`, `study-in-china`
- University cards grid: use `el-row` / `el-col` or Tailwind grid
- If no universities in DB, show "No universities found" empty state
