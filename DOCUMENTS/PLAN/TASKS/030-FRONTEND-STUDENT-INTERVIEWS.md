# TASK-030: Frontend Student Interviews

**Phase:** 9 — Frontend: Student Portal  
**Status:** Pending  
**Depends On:** TASK-021, TASK-013, TASK-018  
**Priority:** HIGH  

---

## Objective

Implement the student's interview management page (list of scheduled/past interviews) and the interview room page where the student joins a TRTC video call with the enterprise representative.

> Note: The actual TRTC SDK integration in the interview room is deferred to TASK-039. This task creates the interview list + a placeholder interview room page that displays connection info.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/student-interviews.html` — Interviews list mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.10 (Interviews)
3. `DOCUMENTS/TRTC_Integration.md` — Section 3 (WebRTC concepts)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.C.3 (Student: Attend Interviews)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/interviews?my=true` | My interviews |
| GET | `/api/interviews/{id}` | Interview detail |
| POST | `/api/interviews/{id}/join` | Get TRTC credentials |

---

## Deliverables

### Student Interviews List Page
**`frontend/src/views/student/InterviewsPage.vue`**

Layout (matches `student-interviews.html`):
1. **Status Tabs** — All / Scheduled / Completed / Cancelled
2. **Interviews List/Table**:
   - Columns: Company Logo + Name, Job Title, Scheduled Date/Time, Duration, Status, Actions
   - Status badges: Scheduled (blue), Ongoing (green), Completed (gray), Cancelled (red)
   - Actions:
     - "Join Interview" button — active 15 minutes before scheduled time (or when status = ongoing)
     - "View Details" — show interview detail modal
3. **Countdown Timer** — for upcoming interviews, show time remaining: "In 2h 30m"

### Interview Detail Modal
- Company: name, logo
- Job: title
- Scheduled: date/time, duration
- Interviewer: name
- Notes: any preparation notes from enterprise
- Status history

### Interview Room Page
**`frontend/src/views/student/InterviewRoomPage.vue`**

This page:
1. On mount: call `POST /api/interviews/{id}/join` to get TRTC credentials
2. Display loading state while fetching credentials
3. Pass credentials to `TrtcRoom` component (placeholder in this task; full implementation in TASK-039)
4. Show: room ID, SDK app ID (debug info in development)
5. Leave Room button → confirm dialog → navigate back to interviews list

Pre-room Check UI (waiting room):
- Camera test: show local video preview
- Microphone test: show audio level indicator
- "Ready to Join" button → enters the room

Error states:
- Interview not found: 404 message
- Not authorized (not your interview): 403 message
- Interview not started yet: "Interview starts at {time}" with countdown

---

## i18n Keys to Add

```json
"interviews": {
  "pageTitle": "My Interviews",
  "status": {
    "scheduled": "Scheduled",
    "ongoing": "In Progress",
    "completed": "Completed",
    "cancelled": "Cancelled"
  },
  "joinInterview": "Join Interview",
  "viewDetails": "View Details",
  "countdownIn": "In {time}",
  "countdownNow": "Starting now",
  "preparation": "Interview Preparation Notes",
  "room": {
    "title": "Interview Room",
    "checkCamera": "Camera Check",
    "checkMic": "Microphone Check",
    "readyToJoin": "Ready to Join",
    "leaveRoom": "Leave Interview",
    "leaveConfirm": "Are you sure you want to leave the interview?"
  }
}
```

---

## Acceptance Criteria

- [ ] Interviews list fetches from API and displays with correct status badges
- [ ] Status tab filter works
- [ ] "Join Interview" button active only when within 15 minutes of scheduled time OR status = ongoing
- [ ] Countdown timer shows time remaining for scheduled interviews
- [ ] Interview detail modal shows all fields
- [ ] Interview room page calls `POST /api/interviews/{id}/join` and receives TRTC credentials
- [ ] Camera/mic check shows local video preview
- [ ] Error states (404, 403, not started) show appropriate messages
- [ ] Leave room button shows confirmation dialog
- [ ] All text via i18n

---

## Notes

- TRTC room component (`<TrtcRoom />`) is a placeholder in this task — it will be replaced in TASK-039 with the real SDK implementation
- "Join Interview" button timing: compare `scheduled_at` with `Date.now()` — enable if within 15 minutes
- Camera access: `navigator.mediaDevices.getUserMedia({ video: true, audio: true })` — handle permission denied gracefully
- Interview list should poll or refresh when status changes (check every 30s for active interviews)
