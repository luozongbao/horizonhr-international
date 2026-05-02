# TASK-027: Frontend Seminar Center (Public)

**Phase:** 8 — Frontend: Public Pages  
**Status:** Pending  
**Depends On:** TASK-021, TASK-014  
**Priority:** MEDIUM  

---

## Objective

Implement the public Seminar Center: seminar list, seminar detail/registration page, live watch page (streaming), and recorded playback page. This is the public-facing half of seminars; admin management is in TASK-036.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/visual-mockups/student-seminars.html` — Seminar browse mockup
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.9 (Seminars), Section 3.11 (Watch/Recording)
3. `DOCUMENTS/TRTC_Integration.md` — Sections 4–6 (Tencent CSS / TRTC CDN Live player, URL signing, recording)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.D.3 (Seminar / Live Broadcast)

---

## API Endpoints Used

| Method | Path | Description |
|--------|------|-------------|
| GET | `/api/seminars` | List seminars (public) |
| GET | `/api/seminars/{id}` | Seminar detail |
| POST | `/api/seminars/{id}/register` | Register for seminar (auth) |
| DELETE | `/api/seminars/{id}/unregister` | Cancel registration (auth) |
| GET | `/api/seminars/{id}/watch` | Get live/recording stream URL |

---

## Deliverables

### Seminar List Page
**`frontend/src/views/public/SeminarListPage.vue`**

Layout:
- Page hero: "Seminar Center" title
- Filter tabs: All / Upcoming / Live Now / Recorded
- Search input
- Seminar cards grid (3 columns desktop):
  - Thumbnail, status badge (Upcoming/Live/Recorded), title, speaker name + photo, date/time, viewer count (for live), "Register" / "Watch Now" / "Watch Recording" button
- Pagination

### Seminar Detail Page
**`frontend/src/views/public/SeminarDetailPage.vue`**

Layout:
- Thumbnail/cover image (full width)
- Title, status badge, date/time, duration, language
- Speaker info: photo, name, title, bio
- Description (rich text)
- Registration section:
  - If `scheduled`: "Register" button (requires login)
  - If already registered: "You're registered" + "Cancel Registration"
  - If `live`: "Watch Live Now" button → `/seminars/{id}/watch`
  - If `ended` + has recording: "Watch Recording" button → `/seminars/{id}/watch`
- Registered count indicator

### Seminar Watch Page
**`frontend/src/views/public/SeminarWatchPage.vue`**

This page handles both live streaming and recording playback:

**Live Mode** (seminar.status === 'live'):
- Full-screen capable video player using HLS.js
- HLS URL from `GET /api/seminars/{id}/watch` response
- Player controls: play/pause, volume, fullscreen
- **Danmu (bullet comments)** overlay:
  - Input bar at bottom: type + send
  - Comments float across the screen right-to-left
  - Polling every 3 seconds for new danmu from API (or WebSocket in future)
  - POST `/api/seminars/{id}/danmu` to send comment (auth required)
- Viewer count (refreshed every 30s)
- Seminar info sidebar: title, speaker

**Recording Mode** (seminar.status === 'ended' + has recording):
- Same HLS.js video player
- Playback speed controls: 0.5x, 1x, 1.25x, 1.5x, 2x
- No danmu input (read-only danmu replay optional)
- Recording URL from API response

**Player Package:**
- Use `hls.js` for HLS stream playback in browser
- Wrap in a `VideoPlayer.vue` component

---

## VideoPlayer Component
**`frontend/src/components/seminar/VideoPlayer.vue`**

Props:
- `src: String` — HLS URL (`.m3u8`)
- `autoplay: Boolean` — default false for recording, true for live
- `controls: Boolean`
- `enableSpeedControl: Boolean` — show speed options for recordings

Uses `hls.js` to attach to a `<video>` element:
```js
import Hls from 'hls.js'
onMounted(() => {
  if (Hls.isSupported()) {
    const hls = new Hls()
    hls.loadSource(props.src)
    hls.attachMedia(videoRef.value)
  } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
    videoRef.value.src = props.src // Safari native HLS
  }
})
```

---

## Danmu Component
**`frontend/src/components/seminar/DanmuOverlay.vue`**

- Floating comments animate from right to left across the video
- Each comment: random Y position (10%–80% of player height), random color from a palette
- Input box at bottom (if authenticated)
- Poll `GET /api/seminars/{id}/danmu?after={lastId}` every 3 seconds

---

## i18n Keys to Add

```json
"seminar": {
  "pageTitle": "Seminar Center",
  "status": {
    "scheduled": "Upcoming",
    "live": "Live Now",
    "ended": "Recorded"
  },
  "register": "Register",
  "registered": "Registered",
  "cancelRegistration": "Cancel Registration",
  "watchLive": "Watch Live",
  "watchRecording": "Watch Recording",
  "viewerCount": "{count} watching",
  "danmuPlaceholder": "Send a comment...",
  "danmuSend": "Send",
  "loginToComment": "Login to send comments",
  "loginToRegister": "Login to register",
  "playbackSpeed": "Speed",
  "noRecording": "Recording not yet available"
}
```

---

## Acceptance Criteria

- [ ] Seminar list fetches from API and displays cards with correct status badges
- [ ] Status filter tabs (All/Upcoming/Live/Recorded) filter the list
- [ ] Seminar detail shows full info including speaker bio
- [ ] "Register" button calls API and shows success
- [ ] "Cancel Registration" calls API and updates UI
- [ ] Live watch page loads HLS stream with `hls.js`
- [ ] Danmu comments appear as floating overlays on the video
- [ ] Danmu input requires login; shows "Login to comment" for guests
- [ ] Recording playback shows speed control (0.5x–2x)
- [ ] Watch page handles both live and recording modes based on seminar status
- [ ] Safari fallback for native HLS playback
- [ ] All text via i18n

---

## Notes

- `hls.js` package: `npm install hls.js`
- Danmu polling is simple HTTP polling — not WebSocket. Acceptable for MVP
- Live seminar: only registered users (or logged-in users) can access the watch page — backend enforces this via `GET /api/seminars/{id}/watch` requiring auth
- Public seminars (if admin configures as public): anyone can watch without login
- Speed control: `videoElement.playbackRate = 1.5`
