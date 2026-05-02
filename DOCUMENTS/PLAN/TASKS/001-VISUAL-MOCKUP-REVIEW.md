# TASK-001: Visual Mockup Review & Corrections

**Phase:** 0 — Design Validation  
**Status:** Pending  
**Depends On:** —  
**Priority:** HIGH  

---

## Objective

Review all existing HTML visual mockups in `DOCUMENTS/DESIGNS/visual-mockups/` and email templates in `DOCUMENTS/DESIGNS/email-templates/` against the project Requirements and API/System Design documents. Identify and fix any pages that are missing required features, have incorrect content, or are inconsistent with the design system. The mockups will serve as the direct visual reference for all frontend development tasks.

---

## Reference Documents

Before starting, read these documents in full:

1. `DOCUMENTS/REQUIREMENTS-EN.md` — Full business requirements (truth source)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — DB schema, modules, traceability
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — All API endpoints
4. `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` — Multi-language form field patterns
5. `DOCUMENTS/DESIGNS/MOCKUP_SETTINGS_MULTI_LANGUAGE.md` — Settings page reference
6. `DOCUMENTS/DESIGNS/visual-mockups/VISUAL_MOCKUPS.md` — Existing mockup index

---

## Existing Mockup Files

### Public / Auth Pages
- `visual-mockups/index.html` — Home page
- `visual-mockups/login.html` — Login page
- `visual-mockups/register-student.html` — Student registration
- `visual-mockups/register-enterprise.html` — Enterprise registration

### Student Portal
- `visual-mockups/student-dashboard.html`
- `visual-mockups/student-profile.html`
- `visual-mockups/student-resume.html`
- `visual-mockups/student-applications.html`
- `visual-mockups/student-interviews.html`
- `visual-mockups/student-seminars.html`

### Enterprise Portal
- `visual-mockups/enterprise-dashboard.html`
- `visual-mockups/enterprise-profile.html`
- `visual-mockups/enterprise-jobs.html`
- `visual-mockups/enterprise-talent.html`
- `visual-mockups/enterprise-interviews.html`

### Admin Portal
- `visual-mockups/admin-dashboard.html`
- `visual-mockups/admin-users.html`
- `visual-mockups/admin-resumes.html`
- `visual-mockups/admin-interviews.html`
- `visual-mockups/admin-seminars.html`
- `visual-mockups/admin-news.html`
- `visual-mockups/admin-settings.html`
- `visual-mockups/admin-language-settings.html`

### Email Templates
- `email-templates/student-verify-email.html`
- `email-templates/password-reset.html`
- `email-templates/interview-invitation.html`
- `email-templates/interview-result.html`
- `email-templates/job-application-received.html`
- `email-templates/enterprise-pending-notify-admin.html`
- `email-templates/enterprise-activated.html`
- `email-templates/seminar-reminder.html`

---

## Review Checklist

### For Each Visual Mockup, Verify:

1. **Colors** — Primary: `#003366`, Secondary: `#E6F0FF`, White: `#FFFFFF`
2. **Navigation** — Header has: company name, nav menu (Home / About / Study in China / Talent Pool / Corporate / Seminars / News / Contact), language selector (EN/ZH/TH), login/register buttons
3. **Language Selector** — Header must include a visible EN/ZH_CN/TH switcher
4. **Footer** — Address, phone, email, WeChat, copyright, Privacy Policy, Terms links
5. **Responsive indicators** — Page should indicate mobile-responsive layout consideration
6. **Role-appropriate content** — Student pages don't show enterprise features and vice versa

### Missing Pages to Create (if not already present):

| Page | Route | Notes |
|------|-------|-------|
| About Us | `/about` | Company intro, history, team |
| Study in China | `/study` | Programs, universities, FAQ |
| Talent Pool (public) | `/talent` | Public view of talent cards |
| Corporate Cooperation | `/corporate` | Enterprise services, partner logos |
| Seminar Center (list) | `/seminars` | Public seminar list |
| Seminar Detail | `/seminars/:id` | Info + register/watch button |
| News List | `/news` | Category filter + list |
| News Detail | `/news/:id` | Article view |
| Contact Us | `/contact` | Form + map + office info |
| Email Confirm Success | — | Post-registration confirmation |
| Admin CMS Pages | `/admin/pages` | Page management |
| Admin CMS Posts | `/admin/posts` | Post/news management |
| Admin Statistics | `/admin/dashboard` | Charts — must include stats widgets |
| Admin Contact Messages | `/admin/contacts` | View submitted contact forms |

### Feature Checks by Page:

**index.html (Home):**
- [ ] Banner/slider (3-4 images with slogan)
- [ ] Core section entries (Study in China, Students, Corporate, Seminar Center)
- [ ] Scrolling logos: partner universities + companies
- [ ] Outstanding student showcase cards
- [ ] Upcoming seminars section
- [ ] 3-4 core advantages section
- [ ] Contact/consultation buttons (WeChat, WhatsApp, Line)

**login.html:**
- [ ] Email + password fields
- [ ] Social login buttons (Google, Facebook, LinkedIn, WeChat)
- [ ] "Forgot password?" link
- [ ] Links to register as student / enterprise

**register-student.html:**
- [ ] Email, password, confirm password
- [ ] Name, nationality, phone fields
- [ ] PDPA consent checkbox
- [ ] "Email confirmation will be sent" note

**register-enterprise.html:**
- [ ] Email, password, company name fields
- [ ] "Account requires admin review" notice
- [ ] PDPA consent checkbox

**student-resume.html:**
- [ ] Upload button accepting PDF/DOC/JPG/PNG ≤20MB
- [ ] Visibility selector (Admin only / Enterprise visible / Public)
- [ ] Status indicator (Pending / Approved / Rejected)
- [ ] Talent card section

**student-interviews.html:**
- [ ] Interview list with status
- [ ] "Join Interview" button (links open without login requirement note)

**admin-dashboard.html:**
- [ ] Statistics widgets: total users, resumes, interviews, seminars, jobs
- [ ] Charts for trends

**admin-settings.html:**
- [ ] Must match `MOCKUP_SETTINGS_MULTI_LANGUAGE.md` design
- [ ] Dynamic language tabs (per active languages)
- [ ] SMTP test button
- [ ] Logo/favicon upload buttons

---

## Deliverables

1. **Reviewed & corrected** versions of all existing mockup HTML files (fix in-place)
2. **New HTML mockup files** for any missing pages identified above
3. Updated `DOCUMENTS/DESIGNS/visual-mockups/VISUAL_MOCKUPS.md` — add new pages to index

---

## Acceptance Criteria

- [ ] All existing mockup files reviewed against requirements
- [ ] All pages use correct brand colors (`#003366`, `#E6F0FF`, `#FFFFFF`)
- [ ] All pages have header with language selector (EN/ZH_CN/TH)
- [ ] All pages have proper footer
- [ ] Home page has all required sections per REQ IV.A.1
- [ ] Login page has social auth buttons (Google, Facebook, LinkedIn, WeChat)
- [ ] Register pages have PDPA consent checkbox
- [ ] Student resume page has visibility selector and upload area
- [ ] Admin dashboard has statistics widgets
- [ ] Admin settings page matches MOCKUP_SETTINGS_MULTI_LANGUAGE.md design
- [ ] All email templates have proper HTML structure, brand colors, unsubscribe footer
- [ ] Missing pages created (About, Study in China, Talent Pool public, Corporate, News, Contact, Seminar Center)
- [ ] VISUAL_MOCKUPS.md updated with complete page index

---

## Notes

- Mockups are **HTML/CSS only** — no JavaScript behavior needed, just visual structure
- Use inline styles or a simple CSS section within each HTML file
- Mockups define the visual target for Vue component implementation in later tasks
- Keep the design clean and professional (REQ V: "Clean, professional, international")
- The color palette is strict — do not introduce new primary colors
