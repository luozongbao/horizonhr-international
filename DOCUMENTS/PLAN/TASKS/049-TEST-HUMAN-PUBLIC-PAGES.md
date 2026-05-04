# TASK-049 — [HUMAN TEST] Frontend: Public Pages & Navigation

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 2 — Frontend Visual & UX  
**Priority:** HIGH  
**Prerequisites:** TASK-048 passed; browser available; app running at `http://10.11.12.30`  
**Estimated Effort:** 45 min  

---

## Description

Manually verify all public-facing frontend pages render correctly, navigation works, content displays in all three languages, and the responsive layout looks correct on desktop and mobile. No login required for these tests.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.A (Frontend Display Modules) |
| Design System | `DOCUMENTS/DESIGNS/DESIGN_SYSTEM.md` | Colors, fonts, components |
| Visual Mockups | `DOCUMENTS/DESIGNS/visual-mockups/` | Reference designs |

---

## Test Environment

- **URL:** `http://10.11.12.30`
- **Browsers to Test:** Chrome (required), Firefox (optional), Mobile Chrome (optional)
- **Primary Colors to Verify:** Dark Blue `#003366`, White `#FFFFFF`, Light Blue `#E6F0FF`

---

## Test Steps

### Group A — Home Page

#### A1. Load Home Page

Open `http://10.11.12.30` in Chrome.

**Check:**
- [ ] Page loads within 3 seconds
- [ ] No console errors (F12 → Console)
- [ ] Header with company name and navigation visible
- [ ] Banner/slider with slogan visible
- [ ] Login/Register buttons in header
- [ ] Footer with contact info visible

#### A2. Home Page Sections

Scroll down the home page.

**Check:**
- [ ] Core section entries visible (Study in China, Talent Pool, Corporate, Seminar)
- [ ] Partner logos section visible (or placeholder)
- [ ] Outstanding students section visible
- [ ] Upcoming seminars section visible
- [ ] Core advantages section (3-4 items)
- [ ] Footer: address, phone, email, copyright

#### A3. Navigation Menu

Click each navigation item.

**Check:**
- [ ] About Us → `/about` loads correctly
- [ ] Study in China → `/study` loads correctly
- [ ] Talent Pool → `/talent` loads correctly
- [ ] Corporate Cooperation → `/corporate` loads correctly
- [ ] Seminar Center → `/seminars` loads correctly
- [ ] News → `/news` loads correctly
- [ ] Contact Us → `/contact` loads correctly

---

### Group B — Language Switching

#### B1. Switch to Chinese

Find the language switcher (top-right header area).

**Check:**
- [ ] Language switcher is visible and accessible
- [ ] Clicking "中文" / "ZH" switches page text to Chinese
- [ ] Navigation items change to Chinese
- [ ] Home page sections show Chinese text
- [ ] Language preference persists on page navigation

#### B2. Switch to Thai

**Check:**
- [ ] Clicking "ภาษาไทย" / "TH" switches page text to Thai
- [ ] Navigation items change to Thai
- [ ] Thai text renders correctly (correct font, no broken characters)

#### B3. Switch Back to English

**Check:**
- [ ] "EN" / "English" restores English text
- [ ] All sections back to English

---

### Group C — About Us Page

Navigate to `/about`.

**Check:**
- [ ] Page title "About Us" visible
- [ ] Company introduction section
- [ ] Service system section
- [ ] Qualification display section (or placeholder)
- [ ] No broken images or layout issues

---

### Group D — Study in China Page

Navigate to `/study`.

**Check:**
- [ ] Recruitment projects section (Vocational, Bachelor's, Master's, etc.)
- [ ] Application guide section or tabs
- [ ] Partner universities section (list or grid)
- [ ] FAQ section
- [ ] "Apply Now" or CTA button visible

---

### Group E — Talent Pool Page

Navigate to `/talent`.

**Check:**
- [ ] Talent cards visible (from seeded data or with placeholder state)
- [ ] Filter options visible (nationality, major, language)
- [ ] Each card shows: photo placeholder, name, nationality, university, job intention
- [ ] Clicking a card or "View Profile" shows detail view

---

### Group F — Corporate Cooperation Page

Navigate to `/corporate`.

**Check:**
- [ ] Enterprise services section
- [ ] Partner company logos or placeholder
- [ ] "Post a Job" / "Register as Enterprise" CTA visible
- [ ] Cooperation cases section (or placeholder)

---

### Group G — Seminar Center Page

Navigate to `/seminars`.

**Check:**
- [ ] Upcoming seminars list (from TASK-048 created seminar or placeholder)
- [ ] Each seminar card shows title, date, speaker
- [ ] "Register" button visible on upcoming seminars
- [ ] Tabs/filters for Upcoming / Past / Playback

---

### Group H — News Page

Navigate to `/news`.

**Check:**
- [ ] News article list visible
- [ ] Category filter visible (Company News, Industry News, etc.)
- [ ] Clicking an article opens detail view
- [ ] Article detail shows title, date, content

---

### Group I — Contact Us Page

Navigate to `/contact`.

**Check:**
- [ ] Company address, phone, email visible
- [ ] Social media links (WeChat, WhatsApp, Line, Facebook, LinkedIn)
- [ ] Contact form with: Name, Contact/Email, Message fields
- [ ] Submit button
- [ ] Submit the form with test data → Success message appears

**Test form submission:**
```
Name: Test Human
Email: human@example.com
Message: Testing the contact form
```

**Check:**
- [ ] Form submits without error
- [ ] Success confirmation shown
- [ ] Check Mailpit at `http://10.11.12.30:8025` for notification email

---

### Group J — Responsive Design (Mobile)

Open Chrome DevTools (F12) → Toggle device toolbar → Select iPhone 12 or similar.

**Check:**
- [ ] Home page layout adapts to mobile width
- [ ] Navigation collapses to hamburger menu on mobile
- [ ] Hamburger menu opens/closes correctly
- [ ] Seminar cards stack vertically on mobile
- [ ] Contact form usable on mobile
- [ ] No horizontal scroll bars on any page

---

## Acceptance Criteria

- [ ] All 7 public pages load without errors
- [ ] No JavaScript console errors on any page
- [ ] Language switching works (EN/ZH_CN/TH) on all pages
- [ ] Navigation links work correctly
- [ ] Home page all sections visible and styled correctly
- [ ] Colors match requirements: `#003366` dark blue primary
- [ ] Contact form submits successfully and email appears in Mailpit
- [ ] Mobile responsive layout works (hamburger menu, stacked layout)
- [ ] Pages load within 3 seconds

---

## Notes for Human Tester

- Take screenshots of any visual issues and note the page URL
- If content shows as translation keys like `nav.home` instead of text — note which language has this issue
- Check fonts: Chinese content should use appropriate font, Thai content should render correctly
- If TRTC-related features are not yet configured, seminar live streams are expected to show a "coming soon" or error state

---

## Next Task

Proceed to **TASK-050** (Human Test — Auth Flow)
