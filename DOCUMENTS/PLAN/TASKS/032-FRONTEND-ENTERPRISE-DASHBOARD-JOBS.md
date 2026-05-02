# TASK-032: Frontend Enterprise Dashboard, Profile & Jobs

**Phase:** 10 — Frontend: Enterprise Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-011  
**Priority:** HIGH  

---

## Objective

Implement the enterprise portal's core pages: Dashboard (stats overview), Company Profile editor, and Job Posting management (CRUD for job listings).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-dashboard.html` — Dashboard mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-profile.html` — Profile mockup
3. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-jobs.html` — Jobs management mockup
4. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.3 (Enterprise), Section 3.4 (Jobs)
5. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.2 (Enterprise Portal)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/enterprise/profile` | Get company profile |
| PUT | `/api/enterprise/profile` | Update company profile |
| POST | `/api/enterprise/logo` | Upload company logo |
| GET | `/api/jobs?my=true` | My job postings |
| POST | `/api/jobs` | Create job posting |
| PUT | `/api/jobs/{id}` | Update job posting |
| DELETE | `/api/jobs/{id}` | Delete job posting |
| PUT | `/api/jobs/{id}/publish` | Publish/unpublish job |
| GET | `/api/applications?job_id={id}` | Applications for a job |

---

## Deliverables

### Enterprise Dashboard
**`frontend/src/views/enterprise/DashboardPage.vue`**

Layout (matches `enterprise-dashboard.html`):
1. **Company Info Card** — logo, company name, status badge (Active/Pending)
2. **Stats Cards** (row):
   - Active Job Postings
   - Total Applications Received
   - Scheduled Interviews
   - Talent Pool Viewed
3. **Recent Applications** — table: student name, job applied for, date, status, "Review" button
4. **Upcoming Interviews** — list: student name, job, date/time, "Join" button
5. **My Jobs** — quick list of recent job postings with status + "View Applications" link

If enterprise status is `pending`: show prominent banner "Your account is pending admin approval. You cannot post jobs until approved."

### Enterprise Profile
**`frontend/src/views/enterprise/ProfilePage.vue`**

Sections:
1. **Company Logo Upload** — `el-upload`, accept JPG/PNG, max 5MB
2. **Company Information Form**:
   - Company Name (required)
   - Industry (select)
   - Company Size (select: 1-50, 51-200, 201-500, 500+)
   - Website URL
   - Contact Email
   - Contact Phone
   - Founded Year
   - Company Description (textarea, multi-language: EN/ZH_CN/TH tabs — use `el-tabs`)
   - Office Locations (multi-line text or tag input)
3. **Contact Person**:
   - Contact Name (required)
   - Contact Position
4. **Submit** → `PUT /api/enterprise/profile`

Multi-language description tabs: each tab has a textarea for one language. The API stores `description_en`, `description_zh_cn`, `description_th`.

Reference: `visual-mockups/enterprise-profile.html`

### Job Postings Management
**`frontend/src/views/enterprise/JobsPage.vue`**

Layout (matches `enterprise-jobs.html`):
1. **Header**: "My Job Postings" + "Post New Job" button
2. **Status Tabs**: All / Draft / Published / Closed
3. **Jobs Table**:
   - Columns: Job Title, Location, Type, Applications Count, Status, Posted Date, Actions
   - Actions: Edit, Toggle Publish/Unpublish, Delete
4. **Job Create/Edit Form** (`el-dialog`):
   - Job Title (multi-language: EN/ZH_CN/TH tabs for `title_en`, `title_zh_cn`, `title_th`)
   - Job Type (full-time / part-time / internship / remote)
   - Location (city, country)
   - Salary Range (min, max, currency, "Not Disclosed" checkbox)
   - Description (multi-language, rich text editor — use `el-input type="textarea"` or a simple rich text)
   - Requirements (multi-language, textarea)
   - Deadline Date
   - Save as Draft / Publish
5. **Delete Confirmation Dialog**

### Application Reviews (per job)
From the Jobs page, "View Applications" button opens:
- `frontend/src/views/enterprise/ApplicationsPage.vue` (or modal)
- Filter by status (pending/reviewed/accepted/rejected)
- Table: student name, nationality, resume status, applied date, actions
- "View Resume" → open presigned URL
- "Change Status" → dropdown: pending / reviewed / accepted / rejected

---

## API Module
**`frontend/src/api/enterprise.js`**
```js
export const enterpriseApi = {
  getProfile(),
  updateProfile(data),
  uploadLogo(formData),
  getJobs(params),
  createJob(data),
  updateJob(id, data),
  deleteJob(id),
  publishJob(id),
  unpublishJob(id),
  getApplicationsForJob(jobId, params),
  updateApplicationStatus(appId, status),
}
```

---

## i18n Keys to Add

```json
"enterprise": {
  "dashboard": {
    "pendingBanner": "Your account is pending admin approval.",
    "stats": {
      "activeJobs": "Active Jobs",
      "applications": "Applications",
      "interviews": "Interviews",
      "talentViewed": "Talent Viewed"
    }
  },
  "profile": {
    "title": "Company Profile",
    "uploadLogo": "Upload Logo",
    "companyInfo": "Company Information",
    "contactPerson": "Contact Person"
  },
  "jobs": {
    "pageTitle": "Job Postings",
    "postNewJob": "Post New Job",
    "editJob": "Edit Job",
    "jobTitle": "Job Title",
    "jobType": "Job Type",
    "location": "Location",
    "salaryRange": "Salary Range",
    "notDisclosed": "Not Disclosed",
    "deadline": "Application Deadline",
    "saveAsDraft": "Save as Draft",
    "publish": "Publish",
    "unpublish": "Unpublish",
    "viewApplications": "View Applications ({count})"
  }
}
```

---

## Acceptance Criteria

- [ ] Dashboard shows company info and stats cards with live data
- [ ] Pending enterprise sees approval banner
- [ ] Profile form pre-fills with current company data
- [ ] Logo upload accepts JPG/PNG and saves correctly
- [ ] Multi-language description tabs show correct content per language
- [ ] Job list fetches with status filter tabs working
- [ ] Create job dialog validates required fields
- [ ] Created job appears in list immediately after save
- [ ] Publish/unpublish toggles job status correctly
- [ ] Delete job shows confirmation dialog
- [ ] Applications per job listed with status update capability
- [ ] "View Resume" opens presigned URL in new tab
- [ ] All text via i18n

---

## Notes

- Multi-language job title fields: `title_en`, `title_zh_cn`, `title_th` — all shown in tabs in the form
- Rich text for job description: use `el-input type="textarea"` for MVP; not a full rich text editor
- Status badge colors: Draft (gray), Published (green), Closed (red)
