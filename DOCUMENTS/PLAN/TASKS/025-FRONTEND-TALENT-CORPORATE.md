# TASK-025: Frontend Talent Pool & Corporate Cooperation Pages

**Phase:** 8 — Frontend: Public Pages  
**Status:** Pending  
**Depends On:** TASK-021, TASK-010, TASK-011  
**Priority:** MEDIUM  

---

## Objective

Implement two public-facing pages:
1. **Talent Pool** — Public showcase of student profiles/resumes (visible resumes only), browsable by enterprises and the public
2. **Corporate Cooperation** — CMS-driven page explaining enterprise partnership opportunities, with a contact/inquiry form

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-talent.html` — Talent pool mockup (enterprise view — reference for layout)
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.5 (Resumes: public listing), Section 3.7 (CMS Pages)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.4 (Talent Pool), IV.A.5 (Corporate Cooperation)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/resumes?status=approved` | Public talent pool listing |
| GET | `/api/resumes/{id}` | Public resume detail |
| GET | `/api/pages/corporate` | Corporate page CMS content |
| POST | `/api/contact` | Contact/inquiry form submission |

---

## Deliverables

### Talent Pool Page
**`frontend/src/views/public/TalentPage.vue`**

Sections:
1. **Page Hero** — Title "Talent Pool", description
2. **Search & Filters**
   - Search by name or skills keyword
   - Filter by nationality (select)
   - Filter by education level (select: bachelor/master/phd)
   - Filter by available for: internship / full-time
3. **Student Cards Grid** (3 columns desktop)
   - Each card: avatar, name, nationality flag, education level, skills tags (max 3 shown), "View Resume" button
   - Cards only show if resume `status = 'approved'` AND student chose to make it public
4. **Resume Detail Modal**
   - Full resume: photo, bio, education history, work experience, skills, languages
   - Download button (PDF) → opens presigned URL in new tab
   - "Invite for Interview" button (only shown for logged-in Enterprise users)

### Corporate Cooperation Page
**`frontend/src/views/public/CorporatePage.vue`**

Sections:
1. **Page Hero** — Title "Corporate Cooperation"
2. **Partnership Benefits** — Feature cards from CMS content
3. **How It Works** — Step-by-step process (from CMS or static)
4. **Success Stories** (optional) — Case study cards
5. **Partner Inquiry Form**
   - Fields: Company Name, Contact Name, Email, Phone, Message
   - Submit → `POST /api/contact` with subject "Corporate Partnership Inquiry"
   - Show success message after submit

---

## i18n Keys to Add

```json
"talent": {
  "pageTitle": "Talent Pool",
  "searchPlaceholder": "Search by name or skills...",
  "filterNationality": "Nationality",
  "filterEducation": "Education Level",
  "filterAvailability": "Available For",
  "viewResume": "View Resume",
  "downloadResume": "Download Resume",
  "inviteInterview": "Invite for Interview",
  "noResults": "No matching candidates found"
},
"corporate": {
  "pageTitle": "Corporate Cooperation",
  "inquiryForm": "Partnership Inquiry",
  "submitInquiry": "Submit Inquiry",
  "inquirySuccess": "Thank you! We will contact you within 2 business days."
}
```

---

## Acceptance Criteria

- [ ] Talent pool page fetches and displays approved public resumes
- [ ] Search by keyword filters results (via API `?search=` or client-side)
- [ ] Nationality, education, availability filters work
- [ ] Resume detail modal shows full resume information
- [ ] PDF download opens presigned URL in new tab
- [ ] "Invite for Interview" button visible only to logged-in enterprise users
- [ ] Corporate page renders CMS content correctly
- [ ] Partnership inquiry form validates required fields
- [ ] Form submits to `POST /api/contact` and shows success message
- [ ] Both pages responsive on mobile

---

## Notes

- Talent pool: only show resumes where `status = 'approved'` — backend enforces this, but frontend filter label shows "approved candidates"
- Resume PDF download: the API returns a presigned URL — open it with `window.open(url, '_blank')`
- "Invite for Interview" button on resume card: if clicked, redirect to `/enterprise/interviews` with pre-filled student ID (or open a quick invite modal)
- Corporate page CMS slug: `corporate`
