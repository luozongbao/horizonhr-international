# TASK-023: Frontend Home Page

**Phase:** 8 — Frontend: Public Pages  
**Status:** Pending  
**Depends On:** TASK-021  
**Priority:** MEDIUM  

---

## Objective

Implement the public-facing Home page with all sections: banner/hero slider, statistics bar, partner university logos carousel, student showcase, upcoming seminars section, site advantages/features section, and CTA. The page must be fully responsive and match the visual mockup.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/index.html` — Home page mockup (PRIMARY)
2. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Brand colors, typography
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.7 (CMS Pages), Section 3.8 (Posts/News), Section 3.9 (Seminars)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/pages/home` | Home page CMS content |
| GET | `/api/universities?per_page=20` | Partner university logos |
| GET | `/api/seminars?status=scheduled&per_page=4` | Upcoming seminars |
| GET | `/api/posts?per_page=3` | Latest news items |

---

## Deliverables

### Page Component
- `frontend/src/views/public/HomePage.vue`

### API Module Addition
- `frontend/src/api/public.js`:
  ```js
  export const publicApi = {
    getHomePage(),          // GET /api/pages/home
    getUniversities(params), // GET /api/universities
    getSeminars(params),    // GET /api/seminars
    getPosts(params),       // GET /api/posts
    // (other public API methods added as needed)
  }
  ```

### Home Page Sections

**Section 1: Hero Banner Slider**
- Full-width banner with background image/color
- Slide content: headline, sub-headline, CTA button
- Auto-play slider (every 5 seconds), navigation arrows, dot indicators
- Content from CMS `pages.home` (JSON fields: `banners` array)
- Use Element Plus `el-carousel` or Vue-native implementation

**Section 2: Statistics Bar**
- Row of 3–4 statistics: "X+ Partner Universities", "X+ Students Placed", "X+ Job Positions"
- Values from CMS home page content or hardcoded with counter animation
- Background: brand primary (`#003366`), white text

**Section 3: About / Introduction**
- Two-column layout: image left, text right
- Content from CMS `pages.home` content field (trimmed excerpt)
- "Learn More" button → `/about`

**Section 4: Partner University Logos**
- Horizontal auto-scrolling carousel of university logos
- Fetch from `GET /api/universities`
- Each logo links to `/study-in-china` (or university website in new tab)
- Marquee-style infinite scroll using CSS animation

**Section 5: Why Choose Us (Advantages)**
- 3–4 cards with icon + title + description
- Content from CMS or static (configurable)
- Use brand colors for icons

**Section 6: Upcoming Seminars**
- Grid of seminar cards (2–3 columns)
- Each card: thumbnail, title, date/time, speaker name
- "Register" button → `/seminars/{id}` (requires login)
- "View All" link → `/seminars`

**Section 7: Latest News**
- 3-column card grid: thumbnail, title, date, excerpt
- "Read More" link per card → `/news/{slug}`
- "View All News" button → `/news`

**Section 8: CTA Banner**
- Full-width section: headline + two buttons ("Register as Student" / "Corporate Cooperation")
- Brand primary background color

---

## Component Structure

```
views/public/HomePage.vue
  components/
    HeroBanner.vue        # Banner slider
    StatsBar.vue          # Statistics counters
    UniversityLogos.vue   # Logo carousel
    WhyChooseUs.vue       # Feature cards
    UpcomingSeminars.vue  # Seminar cards grid
    LatestNews.vue        # News cards grid
    CtaBanner.vue         # Call to action
```

These components can be in `src/components/home/` or inline in the page.

---

## Responsive Design

| Screen | Layout |
|--------|--------|
| Mobile (`< 768px`) | Single column, hamburger nav |
| Tablet (`768–1024px`) | 2-column grid sections |
| Desktop (`> 1024px`) | Full layout matching mockup |

Use Tailwind responsive prefixes: `md:`, `lg:`.

---

## i18n Keys to Add

```json
"home": {
  "stats": {
    "universities": "Partner Universities",
    "students": "Students Placed",
    "jobs": "Job Opportunities"
  },
  "sections": {
    "whyChooseUs": "Why Choose Us",
    "upcomingSeminars": "Upcoming Seminars",
    "latestNews": "Latest News",
    "partnerUniversities": "Partner Universities"
  },
  "cta": {
    "title": "Start Your Journey in China",
    "registerStudent": "Register as Student",
    "corporate": "Corporate Cooperation"
  }
}
```

---

## Acceptance Criteria

- [ ] Hero banner renders with auto-play, arrows, and dots
- [ ] Statistics bar shows correct numbers with animation
- [ ] University logos scroll horizontally (marquee effect)
- [ ] Upcoming seminars fetched from API and displayed in cards
- [ ] Latest news fetched from API and displayed in cards
- [ ] All sections render on mobile without horizontal overflow
- [ ] CTA buttons navigate to correct routes
- [ ] "View All Seminars" / "View All News" links work
- [ ] Page loads without console errors when API returns empty data (graceful empty state)
- [ ] Loading skeletons shown while API calls are pending
- [ ] All text via i18n (no hardcoded English strings)

---

## Notes

- If CMS `pages.home` returns empty content, show default placeholder text (not blank)
- University logo images: use `OssService::publicUrl()` URLs — these are absolute URLs returned by API
- Seminar thumbnails: same — absolute URLs from API response
- Use `el-skeleton` for loading states
