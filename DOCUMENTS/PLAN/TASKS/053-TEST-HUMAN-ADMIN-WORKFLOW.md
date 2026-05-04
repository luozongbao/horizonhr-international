# TASK-053 — [HUMAN TEST] Admin Portal Full Workflow

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 2 — Portal Workflows  
**Priority:** HIGH  
**Prerequisites:** TASK-052 passed; Admin account `admin@horizonhr.com` / `Admin@12345`  
**Estimated Effort:** 50 min  

---

## Description

Manually test the complete Admin portal: user management, resume oversight, CMS management, settings configuration, language translation management, seminar management, and interview oversight. The admin portal is the most feature-rich section.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B.4 (Admin Panel) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Admin endpoints |

---

## Pre-Test

Login as admin: `admin@horizonhr.com` / `Admin@12345`

---

## Test Steps

### Group A — Admin Dashboard

#### A1. View Admin Dashboard

Navigate to `http://10.11.12.30` → Login → Should redirect to `/admin/dashboard`.

**Check:**
- [ ] Dashboard loads at `/admin/dashboard`
- [ ] Statistics overview: Total students, Enterprises, Active jobs, Resumes pending
- [ ] Recent activity visible (or empty state)
- [ ] Full admin navigation visible

---

### Group B — User Management

#### B1. View All Users

Navigate to Admin → Users (or `/admin/users`).

**Check:**
- [ ] User list shows all registered users
- [ ] Columns: Name/Email, Role, Status, Created date
- [ ] Filters: By role (student/enterprise/admin), By status (active/pending/disabled)

#### B2. View Student Detail

Click on the student user (`student.human@example.com`).

**Check:**
- [ ] Student detail page shows: basic info, registered date, email confirmed status
- [ ] Profile summary visible
- [ ] Applications list visible

#### B3. View Enterprise Detail

Click on enterprise user (`enterprise.human@example.com`).

**Check:**
- [ ] Enterprise detail: company name, contact, status
- [ ] Job listings by this enterprise
- [ ] Activation/deactivation button

#### B4. Disable and Re-Enable a User

Click "Disable" on the student account.

**Check:**
- [ ] Status changes to "Disabled"
- [ ] Student can no longer login (test in separate tab if desired)

Then click "Re-enable".

**Check:**
- [ ] Status returns to "Active"

#### B5. Create New Admin User

Navigate to Users → "Add Admin" or similar.

Fill:
```
Name: Test Admin 2
Email: admin2.test@example.com
Password: Admin@12345
Role: Admin
```

**Check:**
- [ ] Admin user created
- [ ] Appears in user list with "admin" role

---

### Group C — Resume Management

#### C1. View All Resumes

Navigate to Admin → Resumes (or `/admin/resumes`).

**Check:**
- [ ] Resume list shows all uploaded resumes
- [ ] Columns: Student name, File name, Upload date, Visibility, Status
- [ ] Filters: By status (pending/approved/rejected), By visibility

#### C2. Review and Approve Resume

Click on a resume that is "Pending" status.

**Check:**
- [ ] Resume preview visible or download link
- [ ] "Approve" and "Reject" actions available

Click "Approve".

**Check:**
- [ ] Status changes to "Approved"

#### C3. Reject a Resume

Find another resume (or same one reset). Click "Reject".

**Check:**
- [ ] Status changes to "Rejected"
- [ ] Optional: Reason field for rejection

---

### Group D — CMS Management

#### D1. Manage Pages

Navigate to Admin → CMS → Pages (or `/admin/pages`).

**Check:**
- [ ] Page list shows site pages (About Us, Study in China, etc.)
- [ ] Each page has edit option

#### D2. Edit a Page

Click "Edit" on "About Us" page.

Update the English content:
```
About Horizon International: We connect Southeast Asian talent with opportunities in China.
```

Switch to Chinese tab, update Chinese content.

**Check:**
- [ ] Tab switching between EN/ZH/TH works
- [ ] Content saves correctly
- [ ] Updated content visible on public `/about` page

#### D3. Manage Posts / News

Navigate to Admin → CMS → Posts (or `/admin/posts`).

**Check:**
- [ ] Post list visible
- [ ] Create new post button available

Create a new post:
```
Title EN: Latest Study Abroad Opportunities 2026
Title ZH: 2026年最新留学机会
Category: Company News
Content EN: Exciting new partnership announcements for 2026...
Status: Published
```

**Check:**
- [ ] Post saves correctly
- [ ] Appears in News section on public site

---

### Group E — Settings Management

#### E1. View System Settings

Navigate to Admin → Settings (or `/admin/settings`).

**Check:**
- [ ] Settings categories visible: Site, Email, Social, TRTC, etc.
- [ ] Current values displayed

#### E2. Update Site Name (Multilingual)

Find Site Name setting. Update:
```
EN: Horizon International Human Resources
ZH: 国际视野人力资源
TH: ฮอไรซอน อินเตอร์เนชั่นแนล ทรัพยากรบุคคล
```

**Check:**
- [ ] All 3 language fields present
- [ ] Save succeeds
- [ ] Site name updates in header/title

#### E3. View Email (SMTP) Settings

Find SMTP/Email settings.

**Check:**
- [ ] SMTP Host, Port, Username, Password, From Address fields visible
- [ ] Current Mailpit config visible: `host=mailpit`, `port=1025`

---

### Group F — Language/Translation Management

#### F1. View Languages

Navigate to Admin → Languages (or `/admin/languages`).

**Check:**
- [ ] Languages list: English (en), Chinese Simplified (zh_CN), Thai (th)
- [ ] Each language shows: name, code, status (active/inactive)

#### F2. Add a Language (Optional)

Test adding a new language: Vietnamese

```
Name: Vietnamese
Code: vi
Status: inactive
```

**Check:**
- [ ] Language added to list
- [ ] Not visible on public language switcher (inactive)

#### F3. Manage Translations

Navigate to Admin → Translations (or `/admin/translations`).

**Check:**
- [ ] Translation key list visible
- [ ] Can search for a key (e.g., `nav.home`)
- [ ] Edit value for each language

Find `nav.home` → Update TH value to `หน้าหลัก` (if not already set).

**Check:**
- [ ] Save succeeds
- [ ] Public site shows updated Thai text for home link

---

### Group G — Seminar Management

#### G1. View All Seminars

Navigate to Admin → Seminars (or `/admin/seminars`).

**Check:**
- [ ] Seminar list shows all seminars
- [ ] Status badges: Upcoming / Live / Ended / Cancelled

#### G2. Create a New Seminar

Click "Create Seminar".

Fill:
```
Title EN: Career Day 2026: How to Get a Job in China
Title ZH: 2026年职业日：如何在华就业
Title TH: วันอาชีพ 2026: วิธีหางานในจีน
Speaker: HR Expert Panel
Date: (pick a future date, 2 weeks out)
Time: 14:00
Duration: 120 minutes
Access: Public
Status: Upcoming
```

**Check:**
- [ ] Seminar created with trilingual titles
- [ ] Visible in public seminar list

#### G3. View Seminar Registrations

Click on the first seminar → "Registrations" tab.

**Check:**
- [ ] List of students who registered
- [ ] Count of registrations shown
- [ ] Can see student details

---

### Group H — Interview Management

#### H1. View All Interviews

Navigate to Admin → Interviews (or `/admin/interviews`).

**Check:**
- [ ] Interview list shows all scheduled interviews
- [ ] Shows student name, enterprise, job, date, status

#### H2. Create Admin-Scheduled Interview

Click "Create Interview".

Fill:
```
Student: (select student.human@example.com)
Enterprise: (select enterprise.human@example.com's company)
Job: (select a job)
Date: (pick a future date)
Duration: 45 minutes
```

**Check:**
- [ ] Interview created
- [ ] Email notification sent to both student and enterprise (check Mailpit)

---

## Acceptance Criteria

- [ ] Admin dashboard loads with correct stats
- [ ] User list accessible with role/status filters
- [ ] Student and enterprise user detail pages work
- [ ] User disable/re-enable works
- [ ] New admin user can be created
- [ ] Resume list shows all resumes with status indicators
- [ ] Admin can approve/reject resumes
- [ ] CMS page edit with EN/ZH/TH tabs works; content updates on public site
- [ ] CMS post creation works; post appears in news section
- [ ] Settings management: site name, SMTP settings viewable and editable
- [ ] Language list shows EN/ZH_CN/TH
- [ ] Translation management allows editing translation values
- [ ] Seminar creation with trilingual fields works
- [ ] Seminar registration list visible under each seminar
- [ ] Interview management: view all, create new, notifications sent

---

## Next Task

Proceed to **TASK-054** (Human Test — TRTC Video Interview)
