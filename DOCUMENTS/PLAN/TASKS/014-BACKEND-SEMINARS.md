# TASK-014: Backend Seminar Module

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-004  
**Priority:** HIGH  

---

## Objective

Implement the online seminar system: seminar CRUD, public registration/reservation, live stream config provisioning, real-time danmu (bullet comments) via WebSocket, recording management, and playback. Live streaming uses Tencent CSS / TRTC CDN (TASK-019); this task handles the database layer, business logic, and API.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.11 (Seminars)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.14–1.2.16.1, 2.3.7
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.6 (Seminar Center), IV.B.3, IV.C.3 (Online Seminar)
4. `DOCUMENTS/TRTC_Integration.md` — Section 4 (Seminar/Live Streaming), Section 5 (Recording)

---

## API Endpoints to Implement

### Public
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/seminars` | None | List upcoming/past seminars |
| GET | `/api/seminars/{id}` | None | Seminar detail |
| POST | `/api/seminars/{id}/register` | None | Register for seminar |
| GET | `/api/seminars/{id}/recording` | Optional | Get recording/playback URL |
| GET | `/api/seminars/{id}/danmu` | None | Get recent danmu messages |
| POST | `/api/seminars/{id}/danmu` | Optional | Send danmu message |

### Admin
| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/admin/seminars` | Admin | List all seminars |
| POST | `/api/admin/seminars` | Admin | Create seminar |
| PUT | `/api/admin/seminars/{id}` | Admin | Update seminar |
| DELETE | `/api/admin/seminars/{id}` | Admin | Delete seminar |
| POST | `/api/admin/seminars/{id}/go-live` | Admin | Start live stream |
| POST | `/api/admin/seminars/{id}/end-live` | Admin | End live stream |

---

## Deliverables

### Controllers
- `app/Http/Controllers/Public/SeminarController.php`
  - `index(Request $request)` — list: filter by status, target_audience; paginated; include registration count
  - `show(int $id)` — full detail including speaker info and whether user is registered
  - `register(RegisterSeminarRequest $request, int $id)` — register email for seminar
  - `recording(int $id)` — return recording info if exists and ended
  - `getDanmu(int $id)` — return recent danmu messages (last 100)
  - `sendDanmu(SendDanmuRequest $request, int $id)` — create danmu message; broadcast via WebSocket

- `app/Http/Controllers/Admin/SeminarController.php`
  - `index(Request $request)` — all seminars all statuses
  - `store(StoreSeminarRequest $request)`
  - `update(UpdateSeminarRequest $request, int $id)`
  - `destroy(int $id)`
  - `goLive(int $id)` — set status=live, get TRTC CSS push URL via TrtcLiveService, return streaming config (TASK-019 stub)
  - `endLive(int $id)` — set status=ended, trigger recording creation

### Form Requests
- `StoreSeminarRequest` / `UpdateSeminarRequest`:
  - title_zh_cn, title_en, title_th (required)
  - desc_zh_cn, desc_en, desc_th
  - speaker_name, speaker_title, speaker_bio, speaker_avatar
    - thumbnail, stream_url (optional, overridden by TrtcLiveService)
  - target_audience: students|enterprises|both
  - permission: public|registered
  - starts_at (required), duration_min
- `RegisterSeminarRequest` — email (required), name (required), phone (optional)
- `SendDanmuRequest` — content (required, max 100 chars), color (hex, optional), position (scroll|top|bottom)

### Jobs
- `app/Jobs/SendSeminarReminderJob.php` — dispatched by scheduled command; sends reminder email 15 min before start to all registrants; uses `email-templates/seminar-reminder.html`

### Console Commands
- `app/Console/Commands/SendSeminarReminders.php` — scheduled every minute; find seminars starting in 15±1 minutes with reminder_sent=false; dispatch `SendSeminarReminderJob` for each registrant; mark reminder_sent=true

### WebSocket / Broadcasting
- Use Laravel Broadcasting with Redis driver
- Channel: `seminar.{id}.danmu` — public channel for danmu
- Event: `App\Events\SeminarDanmuReceived` — broadcast danmu message to all viewers
- Frontend subscribes to this channel to receive real-time danmu

---

## Seminar Registration Logic

1. Check seminar status is `scheduled` (can only register before live starts)
2. Check permission: if `registered`, require email; if `public`, anyone can watch without registering
3. Upsert `seminar_registrations` row (unique on seminar_id + email)
4. Return confirmation; dispatch confirmation email (simple)

---

## Danmu Logic

- Rate limit: 10 danmu per user per minute (use Redis counter)
- Anonymous users use session or IP for rate limiting
- Store danmu in DB (`seminar_messages` table)
- Broadcast via `SeminarDanmuReceived` event to Laravel Echo / WebSocket
- `GET /api/seminars/{id}/danmu` — return last 100 messages, ordered by send_at

---

## Go Live Flow

1. Admin calls `POST /api/admin/seminars/{id}/go-live`
2. System calls `TrtcLiveService` (TASK-019) to get RTMP push URL and txSecret key
3. Update `seminars.stream_url` (pull/playback URL)
4. Update `seminars.status = live`
5. Return streaming config to admin (push URL for broadcasting software or frontend)

---

## End Live Flow

1. Admin calls `POST /api/admin/seminars/{id}/end-live`
2. Set `seminars.status = ended`, `ended_at = now()`
3. Call `TrtcLiveService` to stop stream and trigger TRTC cloud recording
4. Create `seminar_recordings` row when recording is ready (cloud event callback from TRTC — TASK-019)

---

## Recording Response

`GET /api/seminars/{id}/recording`:
```json
{
  "success": true,
  "data": {
    "video_url": "https://cdn.example.com/recordings/seminar-1.m3u8",
    "thumbnail_url": "https://...",
    "duration_sec": 3600,
    "playback_speeds": ["0.5x", "0.75x", "1x", "1.25x", "1.5x", "1.75x", "2x"],
    "default_speed": "1x",
    "view_count": 142
  }
}
```

Only return if seminar status = `ended` and recording exists.

---

## Acceptance Criteria

- [ ] `GET /api/seminars` returns paginated seminar list with registration count
- [ ] `POST /api/seminars/{id}/register` creates registration and upserts on duplicate email
- [ ] Registration works without login (email-based)
- [ ] `SendSeminarReminderJob` dispatched for registrants 15 min before start
- [ ] Admin can create/update/delete seminars
- [ ] `POST /api/admin/seminars/{id}/go-live` sets status=live and returns stream config
- [ ] `POST /api/admin/seminars/{id}/end-live` sets status=ended
- [ ] `GET /api/seminars/{id}/recording` returns recording only for ended seminars
- [ ] `POST /api/seminars/{id}/danmu` stores message and broadcasts event
- [ ] Danmu rate limited to 10 per minute per user/IP
- [ ] `GET /api/seminars/{id}/danmu` returns last 100 messages
- [ ] `SendSeminarReminderJob` marks `reminder_sent=true` after dispatch (no duplicate sends)

---

## Notes

- Tencent CSS / TRTC CDN stream URL and push key generated in TASK-019; stub here with placeholder values
- WebSocket broadcasting: configure Laravel Echo server or use Pusher-compatible driver (Soketi for self-hosted)
- Seminar with `permission=public`: recording also publicly accessible without registration
- Seminar with `permission=registered`: recording requires user to have been registered (check email in registrations table or require login)
- Concurrent viewer counter: update `current_viewers` via Redis incr/decr on join/leave (WebSocket events)
