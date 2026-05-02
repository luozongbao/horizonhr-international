# TASK-040: Frontend TRTC CSS Seminar Live Integration

**Phase:** 12 — Frontend: Integrations  
**Status:** Pending  
**Depends On:** TASK-027, TASK-019  
**Priority:** HIGH  

---

## Objective

Complete the Tencent CSS / TRTC CDN integration in the seminar watch page: production-ready HLS video player with `hls.js`, playback speed controls for recordings, danmu (bullet comment) overlay system with send/receive, and viewer count updates. Upgrades the placeholder player from TASK-027.

---

## Reference Documents

1. `DOCUMENTS/TRTC_Integration.md` — Sections 4, 5, 6 (TRTC CSS Live Streaming, CDN URL format, VOD Recording) — **PRIMARY**
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.9 (Seminars: watch, danmu)
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.D.3 (Live Seminar: danmu, recording, playback)

---

## Packages

```bash
npm install hls.js
```

`hls.js` handles HLS streams directly — compatible with Tencent CSS CDN HLS output. No Tencent-specific web player SDK needed for viewer playback (TRTC SDK `trtc-sdk-js` handles host/co-host broadcasting only).

---

## Deliverables

### Enhanced VideoPlayer Component
**`frontend/src/components/seminar/VideoPlayer.vue`** — upgrade from TASK-027 placeholder

Complete implementation:
```js
import Hls from 'hls.js'

onMounted(async () => {
  if (!props.src) return
  
  if (Hls.isSupported()) {
    hls = new Hls({
      lowLatencyMode: true,      // for live streams
      liveSyncDurationCount: 3,  // buffer for live
      maxLoadingDelay: 4,
    })
    hls.loadSource(props.src)
    hls.attachMedia(videoRef.value)
    hls.on(Hls.Events.MANIFEST_PARSED, () => {
      if (props.autoplay) videoRef.value.play()
    })
    hls.on(Hls.Events.ERROR, handleHlsError)
  } else if (videoRef.value.canPlayType('application/vnd.apple.mpegurl')) {
    // Safari native HLS
    videoRef.value.src = props.src
  }
})

onUnmounted(() => {
  if (hls) { hls.destroy() }
})
```

Props:
- `src: String` — HLS m3u8 URL (from Tencent CSS CDN play domain)
- `isLive: Boolean` — show "LIVE" badge, disable seeking
- `autoplay: Boolean`
- `enableSpeedControl: Boolean`
- `poster: String` — thumbnail shown before play

Features:
- Custom controls bar (hide native browser controls)
- Volume slider
- Fullscreen button
- Live: "LIVE" badge + live dot indicator
- Recording: progress bar + time display + speed selector
- Error state: "Stream unavailable. Please try again." with retry button

### Enhanced Danmu Overlay Component
**`frontend/src/components/seminar/DanmuOverlay.vue`** — upgrade from TASK-027

Complete implementation:

**Danmu Display:**
- Each danmu spawns at random vertical position (5%–85%)
- Each danmu has random color from safe readable palette: `['#ffffff', '#ffeb3b', '#ff80ab', '#80d8ff', '#b9f6ca']`
- Animation: translate from `left: 100vw` to `left: -100%` over 8 seconds (CSS animation)
- Max 20 danmu visible at once (oldest removed when limit reached)
- Danmu font size: 16px with black text shadow for readability

**Danmu Fetching:**
- Poll `GET /api/seminars/{id}/danmu?after={lastId}` every 3 seconds during live
- Append new danmu to animation queue
- `lastId` tracks last received danmu ID to avoid duplicates

**Danmu Input:**
- Bottom bar input: text + "Send" button
- Requires authentication
- Character limit: 50 chars
- On send: `POST /api/seminars/{id}/danmu` with `{ content, seminar_id }`
- On success: add own danmu to overlay immediately (optimistic)
- Rate limit: max 3 danmu per 10 seconds per user (client-side throttle)

**Danmu API Module:**
```js
export const seminarApi = {
  // ... existing methods ...
  getDanmu(seminarId, afterId),
  sendDanmu(seminarId, content),
  getWatchUrl(seminarId),
  getViewerCount(seminarId),
}
```

### Seminar Watch Page Upgrade
**`frontend/src/views/public/SeminarWatchPage.vue`** — upgrade from TASK-027

Complete implementation:

1. **On mount:**
   - Fetch seminar details
   - Call `GET /api/seminars/{id}/watch` to get HLS URL (Tencent CSS CDN m3u8)
   - Start viewer count polling (every 30s)
   - If live: start danmu polling (every 3s)

2. **Live Layout:**
   - Full-width video player (16:9)
   - Danmu overlay on top of video
   - Right sidebar: seminar info + danmu input
   - Bottom bar: send danmu (mobile)

3. **Recording Layout:**
   - Same player, no danmu input (read-only or disabled)
   - Speed control: 0.5x, 0.75x, 1x, 1.25x, 1.5x, 2x
   - Show duration and progress

4. **Error handling:**
   - Stream not started: "Stream not yet available. Please wait..."
   - Auth required: "Please login to watch"
   - Network error: "Connection lost. Retrying..."
   - HLS error types: fatal → show error + retry, non-fatal → log only

---

## i18n Keys to Add/Update

```json
"seminarWatch": {
  "liveNow": "LIVE",
  "viewerCount": "{count} watching",
  "streamNotAvailable": "Stream not yet available. Please wait...",
  "streamEnded": "This stream has ended.",
  "watchRecording": "Watch Recording",
  "danmuPlaceholder": "Say something... (max 50 chars)",
  "danmuSend": "Send",
  "danmuLoginRequired": "Login to send comments",
  "danmuRateLimit": "Please wait before sending again",
  "speedControl": "Playback Speed",
  "retrying": "Connection lost. Retrying...",
  "errorOccurred": "Unable to load stream. Please try again."
}
```

---

## Acceptance Criteria

- [ ] HLS player loads and plays live stream from Tencent CSS CDN URL
- [ ] "LIVE" badge and live indicator visible during live streams
- [ ] Seeking disabled for live streams, enabled for recordings
- [ ] Playback speed control (0.5x–2x) works for recordings
- [ ] Safari native HLS fallback works on iOS/macOS
- [ ] Danmu comments appear as floating overlays with random positions/colors
- [ ] Danmu input sends comment to API, appears on screen immediately
- [ ] Danmu polling fetches new comments every 3 seconds
- [ ] Client-side rate limit prevents spamming (3 per 10s)
- [ ] Viewer count updates every 30 seconds
- [ ] Fatal HLS error shows error message with retry option
- [ ] Stream unavailable shows waiting message (not error)
- [ ] Component cleanup on unmount (destroy hls.js, stop polling)

---

## Notes

- HLS `lowLatencyMode: true` for live — reduces delay at cost of minor buffering
- Danmu CSS animation performance: use `transform` not `left` for GPU acceleration
- Test with an actual Tencent CSS CDN HLS URL — mock URL won't validate player behavior
- Color contrast: all danmu text colors have text-shadow `1px 1px 2px rgba(0,0,0,0.8)` to ensure readability over any video background
- If `TRTC_PLAY_DOMAIN` is not configured: show a public test HLS stream (e.g., a standard test m3u8) for development
- Stream URL (`m3u8`) comes from backend `GET /api/seminars/{id}/watch` — generated by `TrtcLiveService::getPullUrls()` with txSecret auth (TASK-019)
