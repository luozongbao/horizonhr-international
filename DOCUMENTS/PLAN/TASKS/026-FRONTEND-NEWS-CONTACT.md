# TASK-026: Frontend News & Contact Pages

**Phase:** 8 — Frontend: Public Pages  
**Status:** Pending  
**Depends On:** TASK-021, TASK-007, TASK-015  
**Priority:** MEDIUM  

---

## Objective

Implement the News/Articles pages (list + detail) and the Contact Us page with form submission.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/admin-news.html` — News admin mockup (reference for content field structure)
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.8 (Posts/News), Section 3.14 (Contact)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.6 (News), IV.A.8 (Contact Us)
4. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Typography for article content

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/posts` | News list with pagination |
| GET | `/api/posts/{slug}` | Single news article |
| POST | `/api/contact` | Contact form submission |
| GET | `/api/pages/contact` | Contact page CMS content (phone, address) |

---

## Deliverables

### News List Page
**`frontend/src/views/public/NewsListPage.vue`**

Layout:
- Page hero: title "News & Updates"
- Filter bar: category tabs (if categories exist), search input
- Cards grid (3 columns desktop, 1 mobile):
  - Thumbnail image, category badge, title, date, excerpt (150 chars), "Read More" link
- Pagination (Element Plus `el-pagination`)
- Sidebar (optional on desktop): "Popular Posts" or categories

### News Detail Page
**`frontend/src/views/public/NewsDetailPage.vue`**

Layout:
- Breadcrumb: Home → News → Article Title
- Article header: thumbnail (full-width), category badge, title, date, author
- Article body: rich text rendered via `v-html` + DOMPurify
- Social share buttons (optional): Twitter/X, Facebook, LinkedIn, WeChat
- "Related Posts" section (3 cards from same category)
- "Back to News" link

### Contact Us Page
**`frontend/src/views/public/ContactPage.vue`**

Layout:
1. **Page Hero** — Title "Contact Us"
2. **Contact Info Cards** — phone, email, address from CMS `pages.contact`
3. **Contact Form**:
   - Name (required)
   - Email (required)
   - Phone (optional)
   - Subject (required)
   - Message (required, max 2000 chars)
   - Submit button
4. **Map embed** (optional — can be static image or Google Maps iframe if key available)

Form behavior:
- Submit → `POST /api/contact`
- Success: show "Thank you! We'll get back to you shortly."
- Error: show error message
- Rate limit error (429): show "Too many submissions. Please try again later."

---

## i18n Keys to Add

```json
"news": {
  "pageTitle": "News & Updates",
  "readMore": "Read More",
  "publishedOn": "Published on",
  "relatedPosts": "Related Posts",
  "backToNews": "Back to News",
  "noResults": "No articles found"
},
"contact": {
  "pageTitle": "Contact Us",
  "name": "Your Name",
  "email": "Email Address",
  "phone": "Phone Number",
  "subject": "Subject",
  "message": "Message",
  "send": "Send Message",
  "success": "Thank you! We'll get back to you shortly.",
  "tooManyRequests": "Too many submissions. Please try again in an hour."
}
```

---

## Acceptance Criteria

- [ ] News list fetches from API and displays cards in grid
- [ ] Pagination navigates between pages
- [ ] News detail page fetches by slug and renders full article
- [ ] Rich text rendered safely (DOMPurify)
- [ ] Contact form validates all required fields
- [ ] Contact form submits to `POST /api/contact`
- [ ] 429 rate limit error shows appropriate message
- [ ] Contact page CMS content (phone/address) from API
- [ ] Both pages responsive

---

## Notes

- News slugs: backend returns `slug` field in post list — use `<router-link :to="'/news/' + post.slug">`
- Post `content` field is rich HTML — always sanitize before `v-html`
- Contact form rate limit (3 per IP per hour) — backend returns 429; show friendly message
