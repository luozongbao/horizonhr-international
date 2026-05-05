# TASK-054 — [HUMAN TEST] TRTC Video Interview

**Type:** 🧑 HUMAN Test Task  
**Phase:** Test Phase 3 — TRTC Integration  
**Priority:** MEDIUM (requires TRTC credentials)  
**Prerequisites:** TASK-053 passed; Valid TRTC credentials in Admin → Settings; 2 devices or browser windows  
**Estimated Effort:** 30 min  

---

## Description

Manually test the 1-on-1 TRTC video interview feature. This requires valid Tencent RTC credentials configured in Admin Settings. Two participants are required (Enterprise and Student). Test video, audio, text chat, and the screen share controls.

> **SKIP THIS TASK IF:** TRTC credentials are not yet configured. All steps marked as N/A until credentials are set up.

---

## Reference Documents

| Document | Path | Section |
|----------|------|---------|
| TRTC Integration Guide | `DOCUMENTS/TRTC_Integration.md` | Full document |
| Requirements | `DOCUMENTS/REQUIREMENTS-EN.md` | Section IV.C.2 (Video Interview) |

---

## Pre-Requisites Check

### 1. Configure TRTC Credentials

Login as admin → Settings → TRTC / Video Settings.

Set:
```
TRTC SDK App ID: [your TRTC App ID]
TRTC Secret Key: [your TRTC Secret Key]
```

Save settings.

**If credentials are not available, skip to TASK-055 or mark as DEFERRED.**

### 2. Prepare Two Browser Sessions

- **Session A (Enterprise):** Main browser, logged in as enterprise
- **Session B (Student):** Incognito window or different browser, logged in as student

---

## Test Steps

### Group A — Interview Setup

#### A1. Enterprise Creates Interview Invitation

In Session A (Enterprise), navigate to:  
`/enterprise/interviews` → "Schedule Interview"

Fill:
```
Student: student.human@example.com
Job: Senior Frontend Developer
Date: Today + 1 hour (or right now for testing)
Duration: 30 minutes
```

Click "Send Invitation".

**Check:**
- [ ] Invitation sent
- [ ] Interview appears in enterprise's interview list

#### A2. Student Receives Invitation

In Session B (Student), navigate to `/student/interviews`.

**Check:**
- [ ] Interview invitation visible with date/time
- [ ] "Join Interview" button visible

---

### Group B — Entering the Interview Room

#### B1. Enterprise Enters Room

In Session A (Enterprise), click "Join Interview" on the scheduled interview.

**Check:**
- [ ] Browser requests camera/microphone permission
- [ ] Grant permissions
- [ ] TRTC interview room loads
- [ ] Local video preview visible
- [ ] Room ID shown in UI

#### B2. Student Joins Room

In Session B (Student), click "Join Interview" on the same interview.

**Check:**
- [ ] TRTC room loads
- [ ] Student can see and hear the enterprise video

---

### Group C — In-Room Features

#### C1. Two-Way Video and Audio

With both sessions in the room:

**Check (both sides):**
- [ ] Enterprise video visible in student's view
- [ ] Student video visible in enterprise's view
- [ ] Audio transmits both directions

#### C2. Mute/Unmute Audio

Click the microphone button to mute.

**Check:**
- [ ] Microphone icon shows muted state
- [ ] Other participant cannot hear audio
- [ ] Unmuting restores audio

#### C3. Camera On/Off

Click the camera button to turn off video.

**Check:**
- [ ] Camera shows offline/avatar
- [ ] Other participant sees blank or avatar
- [ ] Turning camera back on restores video

#### C4. Text Chat

Type a message in the chat panel (if present).

**Check:**
- [ ] Message sent
- [ ] Other participant sees message in real-time

#### C5. Screen Share (Optional)

Click "Share Screen" button (if available).

**Check:**
- [ ] Screen share prompt opens
- [ ] Select a window/screen to share
- [ ] Other participant sees shared screen
- [ ] Stop sharing returns to webcam view

---

### Group D — Ending the Interview

#### D1. Leave Interview Room

Click "End Call" or "Leave Room".

**Check:**
- [ ] Session ends gracefully
- [ ] Redirected back to interview list page
- [ ] No stuck loading states

#### D2. Enterprise Records Interview Result

In Session A, navigate to the interview → "Record Result".

Set:
```
Result: Pass
Notes: Strong technical background, good communication
```

**Check:**
- [ ] Result saved
- [ ] Status updates to "Completed"
- [ ] Student can see result in their interview history

---

## Acceptance Criteria

- [ ] TRTC credentials correctly configured and tokens generated
- [ ] Both participants can join the interview room
- [ ] Two-way video visible for both participants
- [ ] Two-way audio works
- [ ] Mute/unmute controls function
- [ ] Camera on/off controls function
- [ ] Text chat sends and receives in real-time
- [ ] Interview can be exited cleanly
- [ ] Interview result can be recorded after call

---

## Notes

- TRTC requires internet access to Tencent RTC servers
- If behind a corporate firewall, TURN/STUN ports may need to be opened
- Camera/microphone permissions must be granted in browser
- Test on a device with a working webcam

---

## Next Task

Proceed to **TASK-055** (Human Test — TRTC Live Seminar)
