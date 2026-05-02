# TASK-033: Frontend Enterprise Talent Pool & Interviews

**Phase:** 10 — Frontend: Enterprise Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-012, TASK-013  
**Priority:** HIGH  

---

## Objective

Implement the enterprise portal's talent pool browsing (search/filter student resumes) and interview management (schedule, list, join interview rooms).

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-talent.html` — Talent pool mockup
2. `DOCUMENTS/DESIGNS/visual-mockups/enterprise-interviews.html` — Interviews management mockup
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.5 (Resumes), Section 3.10 (Interviews)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.2 (Enterprise: Interviews)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/resumes?status=approved` | Talent pool (approved resumes) |
| GET | `/api/resumes/{id}` | Resume detail + presigned URL |
| GET | `/api/interviews?my=true` | My interviews |
| POST | `/api/interviews` | Schedule interview |
| PUT | `/api/interviews/{id}` | Update interview |
| PUT | `/api/interviews/{id}/cancel` | Cancel interview |
| POST | `/api/interviews/{id}/join` | Get TRTC credentials |
| PUT | `/api/interviews/{id}/complete` | Mark as complete + set result |

---

## Deliverables

### Enterprise Talent Pool Page
**`frontend/src/views/enterprise/TalentPage.vue`**

Layout (matches `enterprise-talent.html`):
1. **Search & Filters**:
   - Search (name, skills keyword)
   - Nationality filter
   - Education Level filter
   - Available For (internship / full-time)
   - Language filter (speaks EN / ZH_CN / TH)
2. **Student Cards Grid** — same as public talent page but with enterprise-specific actions:
   - Card: avatar, name, nationality, education, skills tags
   - Action buttons: "View Resume", "Invite for Interview"
3. **Resume Detail Drawer/Modal**:
   - Full resume view
   - "Download PDF" button
   - "Invite for Interview" button → opens interview schedule form
4. **Invite for Interview Form** (`el-dialog`):
   - Title (auto-filled: "Interview with {student_name}")
   - Scheduled Date/Time picker
   - Duration (30/45/60/90 minutes)
   - Interviewer Name
   - Notes for student (preparation instructions)
   - Submit → `POST /api/interviews`

Reference: `visual-mockups/enterprise-talent.html`

### Enterprise Interviews Page
**`frontend/src/views/enterprise/InterviewsPage.vue`**

Layout (matches `enterprise-interviews.html`):
1. **"Schedule New Interview" button** — opens schedule form (same as above)
2. **Status Tabs**: All / Scheduled / Ongoing / Completed / Cancelled
3. **Interviews Table**:
   - Columns: Student Name, Job Position, Scheduled Date/Time, Duration, Status, Actions
   - Actions:
     - "Join" button — active 15 min before scheduled time or if ongoing
     - "Edit" — update notes/time (only if scheduled)
     - "Cancel" — with confirmation dialog
     - "Complete" — mark as complete + input result (pass/fail/pending + notes)
4. **Interview Result Modal** (on Complete):
   - Result: Pass / Fail / Pending
   - Notes (textarea)
   - Submit → `PUT /api/interviews/{id}/complete`

### Interview Room Page
**`frontend/src/views/enterprise/InterviewRoomPage.vue`**

Same structure as student's interview room page (TASK-030):
- Call `POST /api/interviews/{id}/join` for TRTC credentials
- Pre-room camera/mic check
- `<TrtcRoom />` placeholder (TASK-039)
- Leave room button

---

## i18n Keys to Add

```json
"talent": {
  "enterprise": {
    "inviteInterview": "Invite for Interview",
    "scheduleInterview": "Schedule Interview"
  }
},
"enterpriseInterviews": {
  "scheduleNew": "Schedule New Interview",
  "title": "Interview Title",
  "scheduledAt": "Scheduled Date & Time",
  "duration": "Duration",
  "durationOptions": {
    "30": "30 minutes",
    "45": "45 minutes",
    "60": "1 hour",
    "90": "1.5 hours"
  },
  "interviewer": "Interviewer Name",
  "notes": "Notes for Candidate",
  "result": "Interview Result",
  "resultOptions": {
    "pass": "Pass",
    "fail": "Fail",
    "pending": "Pending"
  },
  "resultNotes": "Result Notes",
  "markComplete": "Mark as Completed",
  "cancel": "Cancel Interview",
  "cancelConfirm": "Are you sure you want to cancel this interview?",
  "joinInterview": "Join Interview"
}
```

---

## Acceptance Criteria

- [ ] Talent pool loads approved student resumes with search/filter working
- [ ] "Invite for Interview" opens schedule form pre-filled with student info
- [ ] Interview schedule form validates date/time is in the future
- [ ] Created interview appears in interviews list
- [ ] Interview status tabs filter correctly
- [ ] "Join" button active only within 15 minutes or when ongoing
- [ ] "Cancel" button shows confirmation and calls API
- [ ] "Mark Complete" form submits result and updates status
- [ ] Interview room page retrieves TRTC credentials
- [ ] Edit interview updates scheduled time / notes
- [ ] All text via i18n

---

## Notes

- Resume download: `GET /api/resumes/{id}` returns `download_url` (presigned OSS URL) — use `window.open(url, '_blank')`
- Interview scheduling: disable past dates in the date picker with `:disabled-date="(d) => d < new Date()"`
- Student notification: interview invite triggers email notification (backend handles this via TASK-017)
- `<TrtcRoom />` placeholder: same as in TASK-030 — replaced in TASK-039
