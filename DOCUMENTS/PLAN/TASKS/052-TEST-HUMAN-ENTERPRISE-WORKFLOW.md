# TASK-052 — [HUMAN TEST] Enterprise Portal Workflow

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 2 — Portal Workflows  
**Priority:** HIGH  
**Prerequisites:** TASK-051 passed; Enterprise account `enterprise.human@example.com` / `Test@12345` active  
**Estimated Effort:** 40 min  

---

## Description

Manually test the complete Enterprise portal workflow: company profile setup, job posting, talent search, viewing student resumes, sending interview invitations. Verify all enterprise-facing pages and actions work correctly end-to-end.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B.3 (Enterprise Account) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Enterprise Portal sections |

---

## Pre-Test

Login as enterprise: `enterprise.human@example.com` / `Test@12345`

---

## Test Steps

### Group A — Enterprise Dashboard

#### A1. View Dashboard

After login, navigate to `/enterprise/dashboard`.

**Check:**
- [ ] Dashboard loads correctly
- [ ] Summary stats: Active jobs, Total applications, Upcoming interviews
- [ ] Quick navigation to Jobs, Talent, Interviews

---

### Group B — Company Profile Setup

#### B1. Complete Company Profile

Navigate to Profile → Company Profile (or `/enterprise/profile`).

Fill:
```
Company Name: Human Test Corp International
Industry: Technology / IT
Company Size: 50-200 employees
Description: Leading technology company focused on cross-border talent solutions
Website: https://testcorp.example.com
Address: Room 1501, Zhongguancun Science Park, Beijing, China
```

**Check:**
- [ ] All fields accept input
- [ ] Form submits successfully
- [ ] Profile saved and visible

#### B2. Upload Company Logo

Click "Upload Logo" on company profile.

Upload any PNG/JPG image.

**Check:**
- [ ] Logo uploads successfully
- [ ] Logo displayed on company profile page
- [ ] Logo visible on job listings (where company is shown)

---

### Group C — Job Management

#### C1. Create a New Job Posting

Navigate to Jobs → "Post New Job" (or `/enterprise/jobs/create`).

Fill:
```
Job Title: Senior Frontend Developer
Description: We are looking for an experienced Vue.js developer...
Requirements: Vue 3, TypeScript, 3+ years experience
Location: Beijing, China
Job Type: Full-time
Salary Range: 25,000 - 35,000 CNY/month
Nationality Preference: TH, VN, MY
Status: Active
```

**Check:**
- [ ] All form fields work
- [ ] Job created successfully
- [ ] Redirected to job list or detail page

#### C2. Job Appears in Public Talent Pool

Open an incognito window → Navigate to `http://10.11.12.30/talent` or `/jobs`.

**Check:**
- [ ] Newly created job is visible in public listing
- [ ] Job details match what was entered

#### C3. Edit Job Posting

In enterprise dashboard → Jobs → Edit the created job.

Change salary range or description.

**Check:**
- [ ] Edit form pre-fills with current data
- [ ] Update saves correctly
- [ ] Changes visible in job list

#### C4. Toggle Job Status

Set job status to "Paused" or "Inactive".

**Check:**
- [ ] Job disappears from public listing
- [ ] Job still visible in enterprise's own job list with "Paused" status

---

### Group D — Talent Search

#### D1. Browse Talent Pool

Navigate to Talent Search (or `/enterprise/talents`).

**Check:**
- [ ] Student profiles visible (at least the test student from TASK-051)
- [ ] Talent cards show: Name, Nationality, Education, Languages, Job Intention

#### D2. Filter Talent by Criteria

Apply filters:
- Nationality: Thailand
- Education: Bachelor's Degree

**Check:**
- [ ] Filter results narrow correctly
- [ ] No 500 errors from filter combinations

#### D3. View Student Resume Detail

Click on a student profile → "View Resume" or "View Profile".

**Check:**
- [ ] Student detail page opens
- [ ] Basic info visible (name, nationality, education)
- [ ] Resume file download/view available (for enterprise-visible resumes)
- [ ] Private-only resumes NOT accessible from enterprise view

---

### Group E — Interview Management

#### E1. Send Interview Invitation from Talent Page

On a student profile, click "Invite to Interview" or similar.

Fill:
```
Job Position: Senior Frontend Developer (select from dropdown)
Interview Date: (pick a future date)
Interview Time: 10:00 AM
Duration: 60 minutes
Message: We would like to invite you for an interview...
```

**Check:**
- [ ] Invitation form opens
- [ ] Form submits successfully
- [ ] Success message shown
- [ ] Invitation email sent (check Mailpit)

#### E2. View Interview Schedule

Navigate to Interviews (or `/enterprise/interviews`).

**Check:**
- [ ] Interview list shows the invitation
- [ ] Status: "Scheduled"
- [ ] Interview date, time, student name visible
- [ ] "Join Interview" button visible

#### E3. View Applications for a Job

Navigate to Jobs → Select a job → "View Applications".

**Check:**
- [ ] Application list shows students who applied (from TASK-051)
- [ ] Each application shows: Student name, date applied, status
- [ ] "View Resume" link works
- [ ] "Update Status" or "Interview" action available

#### E4. Shortlist an Applicant

Click "Shortlist" or change status of an application.

**Check:**
- [ ] Status updates to "Shortlisted"
- [ ] Change visible in list

---

## Acceptance Criteria

- [ ] Enterprise dashboard loads with summary stats
- [ ] Company profile can be completely filled and saved
- [ ] Company logo uploads and displays correctly
- [ ] Job posting creates successfully with all fields
- [ ] Job is visible in public talent/job listing
- [ ] Job can be edited with changes saved
- [ ] Job status toggle (active/paused) affects public visibility
- [ ] Talent pool shows students with enterprise-visible profiles
- [ ] Talent filter by nationality/education works
- [ ] Student resume accessible for enterprise-visible resumes
- [ ] Interview invitation can be sent from talent page
- [ ] Interview invitation email sent (Mailpit)
- [ ] Interview schedule visible in enterprise interviews list
- [ ] Job applications viewable with student info
- [ ] Application status can be updated

---

## Next Task

Proceed to **TASK-053** (Human Test — Admin Portal Workflow)
