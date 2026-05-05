# TASK-055 — [HUMAN TEST] TRTC Live Seminar Streaming

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 3 — TRTC Integration  
**Priority:** MEDIUM (requires TRTC CSS credentials)  
**Prerequisites:** TASK-054 completed or skipped; TRTC CSS (Cloud Streaming Service) credentials configured  
**Estimated Effort:** 30 min  

---

## Description

Manually test the TRTC-based live seminar streaming feature. An admin or host starts a live stream, and students join as viewers. Test the danmu (bullet comment) feature, Q&A, and viewer count. Also tests seminar playback recording (if enabled).

> **SKIP THIS TASK IF:** TRTC credentials are not yet configured. Mark as DEFERRED.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| TRTC Integration Guide | `DOCUMENTS/TRTC_Integration.md` | Section 4-6 (Live Seminar) |
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.C.3 (Live Seminar) |
| API Documentation | `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` | Seminar TRTC endpoints |

---

## Pre-Requisites Check

### 1. Verify TRTC Live Settings

Login as admin → Settings → TRTC Settings.

Confirm:
- TRTC SDK App ID is set
- TRTC Secret Key is set

### 2. Prepare Two Browser Sessions

- **Session A (Admin/Host):** Main browser, logged in as admin
- **Session B (Viewer/Student):** Incognito window, logged in as student

---

## Test Steps

### Group A — Seminar Setup

#### A1. Find or Create a Seminar

In Session A (Admin), navigate to Admin → Seminars.

Select the seminar created in TASK-053 (Career Day 2026) or create a new one.

**Check:**
- [ ] Seminar exists with status "Upcoming"

#### A2. Start Live Stream

Click on the seminar → "Start Live" or "Go Live" button.

**Check:**
- [ ] Browser requests camera/microphone permission
- [ ] TRTC live room opens
- [ ] "Live" badge or indicator visible
- [ ] Seminar status changes to "Live"

---

### Group B — Viewer Joins Stream

#### B1. Student Views Live Seminar

In Session B (Student), navigate to `/seminars`.

**Check:**
- [ ] Seminar shows "Live Now" badge
- [ ] "Watch Live" button visible

Click "Watch Live".

**Check:**
- [ ] Live stream viewer loads
- [ ] Can see the host's video/screen
- [ ] Audio from host audible
- [ ] Viewer count shows 1+

---

### Group C — Interaction Features

#### C1. Danmu (Bullet Comments)

In Session B (Student), type a comment in the danmu input.

Example: "Great presentation!"

**Check:**
- [ ] Comment appears floating over the video (bullet comment style)
- [ ] Comment visible to both host and viewer
- [ ] Comments scroll/fade across video

#### C2. Q&A Feature

In Session B, submit a question via Q&A panel:

```
Question: What are the scholarship options for Thai students?
```

In Session A (Admin/Host):

**Check:**
- [ ] Question appears in host's Q&A panel
- [ ] Host can mark question as "Answered"
- [ ] Answered questions visible to all viewers

---

### Group D — Stream End & Playback

#### D1. End Live Stream

In Session A (Admin), click "End Stream" or "Stop Live".

**Check:**
- [ ] Stream ends gracefully
- [ ] Seminar status changes to "Ended"
- [ ] Viewer connection closed cleanly

#### D2. Check Playback Recording

Navigate to `/seminars` → Select the ended seminar.

**Check:**
- [ ] Playback recording available (if TRTC recording was configured)
- [ ] Or "Recording will be available shortly" message shown
- [ ] Playback player loads and plays back the recorded session

---

## Acceptance Criteria

- [ ] Admin can start a live seminar stream
- [ ] Seminar status changes to "Live" when streaming
- [ ] Student viewer can see and hear the live stream
- [ ] Viewer count updates in real-time
- [ ] Danmu comments sent by viewers appear on-screen in real-time
- [ ] Q&A submissions visible to host; can be marked answered
- [ ] Host can end the stream gracefully
- [ ] Ended seminar shows playback (or "recording pending" state)

---

## Notes

- Live streaming requires upload bandwidth from host device
- TRTC CSS (Cloud Streaming Service) is different from standard TRTC — ensure correct credentials
- Danmu/bullet comments require WebSocket connection
- If recording is not enabled in TRTC, playback will not be available — this is acceptable

---

## Next Task

Proceed to **TASK-056** (AI Test — Social OAuth)
