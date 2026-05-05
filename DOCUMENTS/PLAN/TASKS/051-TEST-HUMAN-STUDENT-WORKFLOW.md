# TASK-051 — [HUMAN TEST] Student Portal Workflow

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 2 — Portal Workflows  
**Priority:** HIGH  
**Prerequisites:** TASK-050 passed; Student account `student.human@example.com` / `NewTest@12345` active  
**Estimated Effort:** 45 min  

---

## Description

Manually test the complete Student portal workflow: profile completion, resume upload, browsing jobs, submitting applications, and viewing seminar information. Verify all student-facing pages and actions work correctly end-to-end.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.B.2 (Student Account), IV.C.1 (Resume Upload) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Student Portal sections |

---

## Pre-Test

Login as student: `student.human@example.com` / `NewTest@12345`

---

## Test Steps

### Group A — Student Dashboard

#### A1. View Dashboard

Navigate to `http://10.11.12.30` after login.

**Check:**
- [ ] Dashboard page loads at `/student/dashboard`
- [ ] Welcome message with student name
- [ ] Summary cards: Number of applications, interviews, upcoming seminars
- [ ] Quick navigation links to profile, jobs, seminars

---

### Group B — Profile Completion

#### B1. Complete Student Profile

Navigate to Profile → Edit (or `/student/profile`).

Fill in all fields:
```
Full Name: Human Test Student
Nationality: Thailand (TH)
Phone: +66812345678
Date of Birth: 2000-01-15
Gender: Male
Address: Bangkok, Thailand
Education: Bachelor's Degree
University: Test University
Major: Computer Science
Graduation Year: 2024
Languages: English (Fluent), Chinese (Intermediate), Thai (Native)
Job Intention: Full-time, Software Development
Bio: Passionate software developer seeking opportunities in China
```

**Check:**
- [ ] All fields accept input
- [ ] Form submits successfully
- [ ] Success message shown
- [ ] Profile page shows updated information

#### B2. Upload Profile Photo

On profile page, click "Change Photo" or upload area.

Upload any JPG image ≤5MB.

**Check:**
- [ ] Upload dialog opens
- [ ] Image uploads successfully
- [ ] Profile photo displays on page

---

### Group C — Resume Upload

#### C1. Upload Resume PDF

Navigate to Profile → Resumes (or `/student/resumes`).

Click "Upload Resume". Select a PDF file.

**Check:**
- [ ] Upload dialog opens with supported formats visible (PDF, Word, JPG, PNG, ≤20MB)
- [ ] File selection works
- [ ] Upload progress indicator shown
- [ ] Resume appears in list after upload

#### C2. Set Resume Visibility

Click on the uploaded resume → "Edit" or visibility dropdown.

Change visibility to: **Enterprise Visible**

**Check:**
- [ ] Visibility options: Admin Only / Enterprise Visible / Public
- [ ] Visibility change saves correctly
- [ ] Updated visibility shown in list

#### C3. View Resume File

Click "View" or "Download" on the uploaded resume.

**Check:**
- [ ] File opens or downloads correctly
- [ ] PDF/file is accessible

---

### Group D — Browse Jobs

#### D1. Navigate to Jobs Page

Navigate to `/student/jobs` or Jobs in student menu.

**Check:**
- [ ] Job listings visible (from TASK-046 created job or seeded data)
- [ ] Each job card shows: title, company, location, salary range, type
- [ ] Filters visible: Location, Job Type, etc.

#### D2. View Job Detail

Click on a job listing.

**Check:**
- [ ] Job detail page shows full description, requirements, company info
- [ ] "Apply" button visible
- [ ] Company profile link visible

#### D3. Apply for Job

Click "Apply" on the job detail page.

**Check:**
- [ ] Application dialog/form opens
- [ ] Can select which resume to attach
- [ ] Submit application
- [ ] Success message: "Application submitted"

#### D4. Duplicate Application Prevention

Try to apply for the same job again.

**Check:**
- [ ] "Already Applied" indicator on the job
- [ ] Apply button disabled or shows "Applied" state

---

### Group E — Applications Management

#### E1. View My Applications

Navigate to `/student/applications`.

**Check:**
- [ ] Application list shows submitted applications
- [ ] Each item shows: Job title, Company, Status, Date applied
- [ ] Status badge visible (Applied / Reviewed / Shortlisted / Rejected)

#### E2. View Application Detail

Click on an application.

**Check:**
- [ ] Detail shows full job info, resume attached, current status
- [ ] Timeline/history of status changes

---

### Group F — Seminars

#### F1. Browse Seminars

Navigate to `/seminars` (public) or `/student/seminars`.

**Check:**
- [ ] Upcoming seminars visible
- [ ] "Register" button on each unregistered seminar

#### F2. Register for Seminar

Click "Register" on the seminar created in TASK-048.

**Check:**
- [ ] Registration confirmation shown
- [ ] Button changes to "Registered" or "Attending"
- [ ] Confirmation email sent (check Mailpit)

#### F3. View Registered Seminars

Navigate to `/student/seminars` (My Seminars tab).

**Check:**
- [ ] Registered seminar visible in My Seminars list
- [ ] "Join Live" button (or countdown timer) visible for upcoming seminars

---

### Group G — Language Preference

#### G1. Change Preferred Language in Profile Settings

Navigate to Profile → Settings (or Language preference).

Change to: Thai (ภาษาไทย)

**Check:**
- [ ] Setting saves
- [ ] UI switches to Thai language
- [ ] Future emails should be in Thai (verify in Mailpit after next action)

---

## Acceptance Criteria

- [ ] Student dashboard loads with summary stats
- [ ] Student profile can be fully completed and saved
- [ ] Profile photo upload works
- [ ] Resume upload works (PDF), file stored and accessible
- [ ] Resume visibility settings (3 options) work correctly
- [ ] Job listings display correctly with filters
- [ ] Job detail page shows full information
- [ ] Student can apply for a job; application appears in list
- [ ] Duplicate application is prevented (UI shows "Applied" state)
- [ ] My Applications list shows all applications with status
- [ ] Student can register for seminars; confirmation email received
- [ ] Registered seminars appear in My Seminars with Join Live option
- [ ] Language preference change works

---

## Next Task

Proceed to **TASK-052** (Human Test — Enterprise Portal Workflow)
