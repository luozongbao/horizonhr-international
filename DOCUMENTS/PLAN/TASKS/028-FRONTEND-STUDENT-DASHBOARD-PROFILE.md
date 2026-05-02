# TASK-028: Frontend Student Dashboard, Profile & Resume

**Phase:** 9 — Frontend: Student Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-010, TASK-016  
**Priority:** HIGH  

---

## Objective

Implement the Student portal's core pages: Dashboard (overview of activity), Profile (edit personal info + avatar), and Resume (upload/manage resume file, view status).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/student-dashboard.html` — Dashboard mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/student-profile.html` — Profile mockup
3. `DOCUMENTS/DESIGNS/visual-mockups/student-resume.html` — Resume mockup
4. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.5 (Resumes), Section 3.6 (Users: profile update)
5. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C (Student Portal)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/auth/me` | Current user + profile |
| PUT | `/api/users/profile` | Update profile |
| POST | `/api/users/avatar` | Upload avatar |
| GET | `/api/resumes/my` | Get my resumes |
| POST | `/api/resumes` | Upload new resume |
| DELETE | `/api/resumes/{id}` | Delete resume |
| GET | `/api/applications?my=true` | My applications count |
| GET | `/api/interviews?my=true` | My interviews count |
| GET | `/api/seminar-registrations/my` | My seminar registrations |

---

## Deliverables

### Student Dashboard
**`frontend/src/views/student/DashboardPage.vue`**

Layout (matches `student-dashboard.html`):
1. **Welcome Banner** — "Welcome back, {name}!" + avatar
2. **Stats Cards** (row of 4):
   - Applications Submitted
   - Interviews Scheduled
   - Seminars Registered
   - Resume Status (badge: Pending / Approved / Rejected)
3. **Recent Applications** — table: company, job title, date applied, status badge (last 5)
4. **Upcoming Interviews** — list: company, date/time, join button
5. **Upcoming Seminars** — list: title, date/time, watch button
6. **Profile Completion** — progress bar (0–100%) showing how complete the profile is

Reference: `visual-mockups/student-dashboard.html`

### Student Profile
**`frontend/src/views/student/ProfilePage.vue`**

Sections:
1. **Avatar Upload**
   - `<el-upload>` component with crop preview
   - Accepts JPG/PNG only, max 5MB
   - On submit: `POST /api/users/avatar`

2. **Personal Information Form**
   - Name (required)
   - Email (read-only)
   - Phone
   - Date of Birth
   - Nationality (select)
   - Current City
   - Bio / Introduction (textarea, max 500 chars)
   - Preferred Language (`prefer_lang`: en / zh_cn / th)
   - Submit → `PUT /api/users/profile`

3. **Social Accounts** (linked accounts list)
   - Show: Google, Facebook, LinkedIn, WeChat
   - Connected: show linked email + "Unlink" button
   - Not connected: "Link Account" button → OAuth flow

4. **Change Password** (accordion/collapsible)
   - Current Password, New Password, Confirm Password
   - Submit → `PUT /api/auth/password/change`

Reference: `visual-mockups/student-profile.html`

### Student Resume Manager
**`frontend/src/views/student/ResumePage.vue`**

Layout:
1. **Current Resume Status Card**
   - Status badge: Pending / Approved / Rejected
   - If rejected: show rejection reason
   - Last uploaded date
   - File name / file size

2. **Resume Upload**
   - Drag-and-drop or click to upload
   - Accepted: PDF, DOC, DOCX, JPG, PNG
   - Max size: 20MB (enforce client-side before upload)
   - Progress bar during upload
   - On success: refresh status

3. **Resume Preview** (if approved)
   - "View Resume" button → opens presigned URL in new tab
   - "Download" button → same URL with download header

4. **Upload History** (optional)
   - Table: filename, uploaded date, status
   - Delete button (removes specific resume version)

Reference: `visual-mockups/student-resume.html`

---

## API Module
**`frontend/src/api/student.js`**
```js
export const studentApi = {
  getProfile(),
  updateProfile(data),
  uploadAvatar(formData),
  getResumes(),
  uploadResume(formData),
  deleteResume(id),
  getDashboardStats(),
}
```

---

## i18n Keys to Add

```json
"student": {
  "dashboard": {
    "welcome": "Welcome back, {name}!",
    "stats": {
      "applications": "Applications",
      "interviews": "Interviews",
      "seminars": "Seminars",
      "resumeStatus": "Resume Status"
    },
    "recentApplications": "Recent Applications",
    "upcomingInterviews": "Upcoming Interviews",
    "upcomingSeminars": "Upcoming Seminars",
    "profileCompletion": "Profile Completion"
  },
  "profile": {
    "title": "My Profile",
    "avatar": "Profile Photo",
    "personalInfo": "Personal Information",
    "changePassword": "Change Password",
    "socialAccounts": "Linked Social Accounts"
  },
  "resume": {
    "title": "My Resume",
    "status": {
      "pending": "Under Review",
      "approved": "Approved",
      "rejected": "Rejected"
    },
    "upload": "Upload Resume",
    "dragDrop": "Drag & drop your resume here, or click to browse",
    "allowedFormats": "PDF, DOC, DOCX, JPG, PNG (max 20MB)",
    "viewResume": "View Resume",
    "download": "Download",
    "rejectionReason": "Rejection Reason"
  }
}
```

---

## Acceptance Criteria

- [ ] Dashboard shows 4 stats cards with live data from API
- [ ] Dashboard shows recent applications table
- [ ] Dashboard shows upcoming interviews list with join button
- [ ] Dashboard shows profile completion percentage
- [ ] Profile form pre-fills with current user data
- [ ] Avatar upload accepts JPG/PNG and previews before saving
- [ ] Profile form saves and shows success notification
- [ ] Resume upload accepts correct file types and enforces 20MB limit client-side
- [ ] Upload progress shown during upload
- [ ] Resume status displayed correctly with appropriate badge color
- [ ] Rejected resume shows rejection reason
- [ ] "View Resume" opens presigned URL in new tab
- [ ] All text via i18n

---

## Notes

- Avatar upload: use `el-upload` with `:before-upload` hook to validate type/size before sending
- Resume upload: same approach — validate type and size client-side
- Profile completion %: calculate client-side based on which fields are filled (name, avatar, bio, nationality, phone, resume uploaded)
- `prefer_lang` change in profile: also trigger `languageStore.switchLanguage()` to update UI language immediately
