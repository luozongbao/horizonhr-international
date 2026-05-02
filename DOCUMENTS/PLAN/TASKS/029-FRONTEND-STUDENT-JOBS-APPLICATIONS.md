# TASK-029: Frontend Student Jobs & Applications

**Phase:** 9 — Frontend: Student Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-012  
**Priority:** HIGH  

---

## Objective

Implement the student-facing job browsing and application management pages: a job listing page where students can search and apply for positions, and an applications tracking page.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/student-applications.html` — Applications mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.4 (Jobs), Section 3.10 (Applications)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C.2 (Student: Browse Jobs and Apply)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/jobs` | List published jobs |
| GET | `/api/jobs/{id}` | Job detail |
| POST | `/api/applications` | Submit application |
| GET | `/api/applications?my=true` | My applications |
| DELETE | `/api/applications/{id}` | Withdraw application |

---

## Deliverables

### Job Browsing Page
**`frontend/src/views/student/JobsPage.vue`** (and also accessible as a public page `/jobs`)

> Note: Job browsing is visible to all users but applying requires login as student.

Layout:
1. **Filter Bar**
   - Search input (job title, keyword)
   - Location filter (city/country)
   - Job type (full-time, part-time, internship, remote)
   - Industry (select)
   - "Search" button
2. **Job Cards List** (left column 2/3, sidebar 1/3 on desktop)
   - Card: company logo, job title, company name, location, job type badge, salary range (if disclosed), posted date
   - Click → show job detail in side panel (desktop) or navigate to modal/page (mobile)
3. **Job Detail Panel**
   - Company logo + name + size + industry
   - Job title, type, location, salary
   - Full description (rich text)
   - Requirements list
   - "Apply Now" button (if logged in + is student)
   - "Login to Apply" if not logged in
   - "Already Applied" badge if student already applied

### My Applications Page
**`frontend/src/views/student/ApplicationsPage.vue`**

Layout (matches `student-applications.html`):
1. **Status Tabs** — All / Pending / Reviewed / Accepted / Rejected
2. **Applications Table**:
   - Columns: Company, Job Title, Applied Date, Status badge, Actions
   - Status badges: Pending (gray), Reviewed (blue), Accepted (green), Rejected (red)
   - Actions: "View Job", "Withdraw" (only if status=pending)
3. **Withdraw Confirmation Dialog** — "Are you sure you want to withdraw this application?"

### Quick Apply Modal
When student clicks "Apply Now":
- If student has no approved resume: show warning "Please upload and get resume approved before applying"
- If approved resume exists: confirm dialog "Apply for [Job Title] at [Company]?"
- On confirm: `POST /api/applications` with `job_id`
- Show success notification

---

## API Module
**`frontend/src/api/jobs.js`**
```js
export const jobsApi = {
  getJobs(params),    // filters: search, location, type, industry
  getJob(id),
  applyForJob(jobId),
  getMyApplications(params), // filters: status
  withdrawApplication(id),
}
```

---

## i18n Keys to Add

```json
"jobs": {
  "pageTitle": "Job Opportunities",
  "searchPlaceholder": "Search jobs...",
  "filterLocation": "Location",
  "filterType": "Job Type",
  "filterIndustry": "Industry",
  "jobTypes": {
    "full_time": "Full-time",
    "part_time": "Part-time",
    "internship": "Internship",
    "remote": "Remote"
  },
  "applyNow": "Apply Now",
  "alreadyApplied": "Already Applied",
  "loginToApply": "Login to Apply",
  "noResumeWarning": "Please upload and get your resume approved before applying.",
  "applyConfirm": "Apply for {jobTitle} at {company}?",
  "applySuccess": "Application submitted successfully!"
},
"applications": {
  "pageTitle": "My Applications",
  "status": {
    "pending": "Pending",
    "reviewed": "Reviewed",
    "accepted": "Accepted",
    "rejected": "Rejected"
  },
  "withdraw": "Withdraw",
  "withdrawConfirm": "Are you sure you want to withdraw this application?",
  "withdrawSuccess": "Application withdrawn."
}
```

---

## Acceptance Criteria

- [ ] Job list fetches from API with correct pagination
- [ ] Search + filter controls update the job list
- [ ] Job detail panel/modal shows full job information
- [ ] "Apply Now" checks for approved resume before allowing application
- [ ] Application submission calls `POST /api/applications` and shows success
- [ ] Applied jobs show "Already Applied" badge
- [ ] Applications page lists all applications with correct status badges
- [ ] Status tab filter works
- [ ] "Withdraw" button shows confirmation dialog
- [ ] Withdrawal calls API and removes from list
- [ ] All text via i18n

---

## Notes

- Add `/jobs` as a public route (accessible without login) in the router — shows jobs list without apply button for non-students
- After applying, update the local state to show "Already Applied" without refetching all jobs
- Job location and type are multi-language fields — use `$t()` for job type labels
