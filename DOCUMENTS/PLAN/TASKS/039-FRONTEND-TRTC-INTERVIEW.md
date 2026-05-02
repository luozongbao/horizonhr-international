# TASK-039: Frontend TRTC Video Interview Integration

**Phase:** 12 — Frontend: Integrations  
**Status:** Pending  
**Depends On:** TASK-030, TASK-033, TASK-018  
**Priority:** HIGH  

---

## Objective

Implement the complete TRTC WebRTC video call experience in the interview room pages for both student and enterprise portals. Replace the `<TrtcRoom />` placeholder from TASK-030 and TASK-033 with the real SDK implementation: joining rooms, displaying local/remote video, audio/video controls, screen sharing, and text chat.

---

## Reference Documents

1. `DOCUMENTS/TRTC_Integration.md` — Sections 3, 7 (TRTC Web SDK, Room Management, Integration Steps) — **PRIMARY**
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.10 (Interviews: join endpoint)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.D.2 (Video Interview Requirements)

---

## TRTC SDK Package

Per `DOCUMENTS/TRTC_Integration.md` Section 3:
```bash
npm install trtc-js-sdk
```
Or load from CDN if preferred.

---

## Deliverables

### TRTC Room Component
**`frontend/src/components/interview/TrtcRoom.vue`**

Props:
```ts
{
  sdkAppId: Number,       // from join API response
  roomId: Number,         // interview TRTC room ID
  userId: String,         // e.g. "student_42"
  userSig: String,        // signed token from backend
  displayName: String,    // shown on video tile
  role: 'student' | 'enterprise'
}
```

#### Layout
- **Main video area**: large tile for remote participant
- **Self video tile**: small picture-in-picture (bottom right corner), draggable
- **Controls bar** (bottom):
  - Mute/Unmute microphone toggle
  - Camera on/off toggle
  - Screen share toggle
  - Text chat toggle (sidebar panel)
  - Leave room button (red)

#### TRTC Integration Steps (from TRTC_Integration.md Section 7)
```js
import TRTC from 'trtc-js-sdk'

// 1. Create client
const client = TRTC.createClient({
  mode: 'rtc',
  sdkAppId: props.sdkAppId,
  userId: props.userId,
  userSig: props.userSig,
})

// 2. Subscribe to events
client.on('stream-added', (event) => {
  client.subscribe(event.stream)
})
client.on('stream-subscribed', (event) => {
  event.stream.play('remote-video-container')
})
client.on('peer-leave', () => { /* handle disconnect */ })

// 3. Join room
await client.join({ roomId: props.roomId })

// 4. Create and publish local stream
const localStream = TRTC.createStream({
  userId: props.userId,
  audio: true,
  video: true,
})
await localStream.initialize()
localStream.play('local-video-container')
await client.publish(localStream)
```

#### Microphone/Camera Toggle
```js
// Mute mic
await localStream.muteAudio()
// Unmute mic
await localStream.unmuteAudio()
// Turn off camera
await localStream.muteVideo()
// Turn on camera
await localStream.unmuteVideo()
```

#### Screen Share
```js
const screenStream = TRTC.createStream({
  userId: props.userId,
  audio: false,
  screen: true,
})
await screenStream.initialize()
await client.publish(screenStream)
// Stop screen share: unpublish screenStream
```

#### Leave Room
```js
await client.unpublish(localStream)
localStream.stop()
client.leave()
```

### Text Chat Component
**`frontend/src/components/interview/TextChat.vue`**

- Side panel (toggle show/hide)
- Message list: sender name, message, timestamp
- Input bar with send button
- Backend chat endpoint: `POST /api/interviews/{id}/messages` + `GET /api/interviews/{id}/messages`
- Simple polling every 2 seconds (not WebSocket for MVP)

### Interview Room Pages Update
Update both `student/InterviewRoomPage.vue` and `enterprise/InterviewRoomPage.vue`:
- Replace `<TrtcRoom />` placeholder with the real component
- Pass credentials from the join API response
- Handle errors (browser permission denied, network error, room full)
- Auto-call `client.leave()` on page unmount (`onUnmounted`)

---

## Browser Permission Handling

```js
try {
  await localStream.initialize()
} catch (err) {
  if (err.name === 'NotAllowedError') {
    // Show: "Please allow camera and microphone access"
  } else if (err.name === 'NotFoundError') {
    // Show: "No camera/microphone found"
  }
}
```

---

## i18n Keys to Add

```json
"interviewRoom": {
  "mute": "Mute",
  "unmute": "Unmute",
  "cameraOn": "Camera On",
  "cameraOff": "Camera Off",
  "shareScreen": "Share Screen",
  "stopSharing": "Stop Sharing",
  "chat": "Chat",
  "leaveRoom": "Leave Interview",
  "leaveConfirm": "Leave this interview?",
  "waitingForParticipant": "Waiting for the other participant...",
  "participantLeft": "The other participant has left the interview.",
  "permissionDenied": "Camera and microphone permission required. Please allow access in your browser.",
  "noDeviceFound": "No camera or microphone found.",
  "connectionError": "Connection error. Please check your internet and try again."
}
```

---

## Acceptance Criteria

- [ ] `TrtcRoom` component joins TRTC room using provided credentials
- [ ] Local video shows in small PiP tile
- [ ] Remote participant video shows in main tile
- [ ] Mute/unmute microphone works without disconnecting
- [ ] Camera on/off works without disconnecting
- [ ] Screen share starts and stops correctly
- [ ] Text chat sends and receives messages
- [ ] Leave room cleans up streams and client, navigates back to interviews list
- [ ] Permission denied error shows user-friendly message
- [ ] No device found error handled
- [ ] Component unmount triggers `client.leave()` (no hanging connections)
- [ ] Works in Chrome, Firefox, Edge (main TRTC-supported browsers)

---

## Notes

- TRTC Web SDK does NOT support Safari/iOS on some versions — show warning if unsupported browser detected
- `TRTC.checkSystemRequirements()` — call this first; show error if requirements not met
- For development without TRTC credentials: render a mock UI with a static video placeholder
- Text chat messages: `GET /api/interviews/{id}/messages` — add this endpoint in backend if not in TASK-013 (simple messages table with interview_id, sender_id, content, created_at)
- Only 2 participants per room (1 student + 1 enterprise) — no group video needed
