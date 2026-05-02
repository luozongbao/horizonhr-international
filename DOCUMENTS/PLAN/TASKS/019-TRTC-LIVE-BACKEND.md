# TASK-019: TRTC CSS Seminar Live Streaming Backend

**Phase:** 5 — External Service Integrations (Backend)  
**Status:** Pending  
**Depends On:** TASK-014  
**Priority:** HIGH  

---

## Objective

Implement the Tencent CSS (Cloud Streaming Services) / TRTC CDN backend for seminars: stream key/URL generation for RTMP push and HLS/FLV pull, TRTC cloud event callback receiver for live status updates and recording completion, and completing all streaming stubs left in TASK-014.

---

## Reference Documents

1. `DOCUMENTS/TRTC_Integration.md` — Sections 4–6 (TRTC Live Streaming, CDN, Recording) — **PRIMARY**
2. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.11 (Seminars: recording/live endpoints)
3. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 2.3.6 (Seminar Module)
4. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.D.3 (Seminar / Live Broadcast)

---

## TRTC CSS Live Streaming Concepts (from TRTC_Integration.md Section 4)

| Concept | Description |
|---------|-------------|
| Push URL | OBS/encoder uploads stream to this RTMP URL (Tencent CSS push domain) |
| Pull URL | Viewer watches via HLS (m3u8), FLV, or RTMP (Tencent CSS play domain) |
| Stream Key | Unique identifier for the stream (`seminar_{id}_{random}`) |
| txSecret | MD5 signed token to authenticate push/pull (Tencent txSecret signing) |
| Recording | TRTC cloud recording → stored in VOD (Video on Demand) |
| Domain groups | Push domain separate from play domain (both configured in Tencent Console) |

---

## Deliverables

### Service
- `app/Services/TrtcLiveService.php`
  ```php
  class TrtcLiveService {
      // Generate authenticated RTMP push URL for OBS/encoder
      // Uses Tencent txSecret signing: txSecret=md5(key+streamId+expireTime)&txTime=expireHex
      public function getPushUrl(string $streamKey, int $expireSeconds = 7200): string
      
      // Generate pull URLs for viewers (HLS, RTMP, FLV)
      public function getPullUrls(string $streamKey): array
      // Returns: ['hls' => 'https://...', 'rtmp' => 'rtmp://...', 'flv' => 'https://...']
      
      // Generate Tencent txSecret signed token
      // txSecret = MD5(key + streamId + txTime_hex)
      // txTime = hex(expire_timestamp)
      private function generateTxSecret(string $streamKey, int $expireTime, string $authKey): string
      
      // Generate a unique stream key for a seminar
      public function generateStreamKey(int $seminarId): string
      // Returns: "seminar_{seminarId}_{random8chars}"
  }
  ```

### Config
Add to `config/services.php` under `tencent.trtc` (same block as TASK-018):
  ```php
  'tencent' => [
      'trtc' => [
          'app_id'     => env('TRTC_SDK_APP_ID'),
          'secret_id'  => env('TRTC_SECRET_ID'),
          'secret_key' => env('TRTC_SECRET_KEY'),
          'api_zone'   => env('TRTC_API_ZONE', 'gzjp'),
          // CSS Live Streaming (Seminar)
          'push_domain' => env('TRTC_PUSH_DOMAIN'),
          'play_domain' => env('TRTC_PLAY_DOMAIN'),
          'push_key'    => env('TRTC_PUSH_KEY'),   // txSecret signing key for push
          'play_key'    => env('TRTC_PLAY_KEY'),   // txSecret signing key for pull
          'app_name'    => env('TRTC_APP_NAME', 'live'),
      ],
  ],
  ```
- Add to `.env.example`:
  ```
  # Tencent CSS Live Streaming (Seminar)
  TRTC_PUSH_DOMAIN=
  TRTC_PLAY_DOMAIN=
  TRTC_PUSH_KEY=
  TRTC_PLAY_KEY=
  TRTC_APP_NAME=live
  ```

### Update Seminar Create (TASK-014 stub)
In `app/Http/Controllers/Admin/SeminarController.php::store()`:
1. After creating seminar record, generate stream key via `TrtcLiveService::generateStreamKey($seminar->id)`
2. Store in `seminars.stream_key`
3. Return push/pull URLs in response (do NOT store in DB — regenerate on demand with fresh txSecret each request)

### New API Endpoint: Get Live URLs
`GET /api/admin/seminars/{id}/live-urls` (Admin only):
- Return push URL (for OBS/encoder) and pull URLs (for viewers)
- Regenerate txSecret tokens each time (short-lived, hex-encoded expiry)

### Cloud Event Callback Endpoint
`POST /api/webhooks/trtc-live` (no auth — validate via Tencent callback signature):
- Handle event types from `DOCUMENTS/TRTC_Integration.md` Section 6:
  - `stream.started` / `PushBegin` → update `seminars.status = 'live'`, set `seminars.started_at`
  - `stream.stopped` / `PushEnd` → update `seminars.status = 'ended'`, set `seminars.ended_at`
  - `recording.complete` / `VodRecord` → store VOD URL in `seminars.recording_url`, set `seminars.recording_status = 'available'`
- Verify Tencent callback signature from request header before processing
- Route: `routes/api.php` → `Route::post('/webhooks/trtc-live', [TrtcLiveWebhookController::class, 'handle'])`

### Controller
- `app/Http/Controllers/Admin/SeminarLiveController.php`
  - `getLiveUrls(int $id)` — return push + pull URLs for admin
- `app/Http/Controllers/Webhook/TrtcLiveWebhookController.php`
  - `handle(Request $request)` — process TRTC cloud event callbacks

### Public Pull URLs Endpoint
`GET /api/seminars/{id}/watch` (authenticated users / public):
- If seminar is `live`: return HLS pull URL (no push URL)
- If seminar is `ended` and has recording: return VOD recording URL
- If seminar is `scheduled`: return 404 with message `"Seminar not started yet"`

---

## URL Auth Signing — Tencent txSecret

From `DOCUMENTS/TRTC_Integration.md` Section 5:
```
txTime  = hex(expire_unix_timestamp)        # e.g. 5C2A3CFF
txSecret = MD5(key + streamId + txTime)    # lowercase hex MD5
signed_url = "{base_url}?txSecret={txSecret}&txTime={txTime}"
```

Example push URL:
```
rtmp://push.example.com/live/seminar_42_abc123?txSecret=9dbc12f1...&txTime=5C2A3CFF
```

Example pull URLs:
```
https://play.example.com/live/seminar_42_abc123.m3u8?txSecret=...&txTime=...
https://play.example.com/live/seminar_42_abc123.flv?txSecret=...&txTime=...
rtmp://play.example.com/live/seminar_42_abc123?txSecret=...&txTime=...
```

Push and pull use **different** signing keys (`TRTC_PUSH_KEY` vs `TRTC_PLAY_KEY`). Generate fresh signed URLs each request — do not cache.

---

## Acceptance Criteria

- [ ] `TrtcLiveService::getPushUrl()` generates valid RTMP push URL with txSecret auth
- [ ] `TrtcLiveService::getPullUrls()` generates HLS, FLV, and RTMP pull URLs with txSecret
- [ ] Stream key generated and stored in `seminars.stream_key` on seminar creation
- [ ] `GET /api/admin/seminars/{id}/live-urls` returns push + pull URLs
- [ ] Webhook/callback `stream.started` event updates seminar status to `live`
- [ ] Webhook/callback `stream.stopped` event updates seminar status to `ended`
- [ ] Webhook/callback `recording.complete` event stores VOD recording URL
- [ ] `GET /api/seminars/{id}/watch` returns HLS URL when seminar is live
- [ ] `GET /api/seminars/{id}/watch` returns recording URL when seminar ended
- [ ] Tencent callback signature verified before processing
- [ ] All env variables documented in `.env.example`

---

## Notes

- Reference `DOCUMENTS/TRTC_Integration.md` Sections 4–6 for exact URL format, txSecret signing algorithm, and cloud event payload structure
- The `TrtcLiveService` class follows the pattern in `TRTC_Integration.md` Section 4 (`TRTCLiveService`)
- Same SDK package as TASK-018: `tencentcloud/tencentcloud-sdk-php-intl` — no extra Composer dependency needed
- For development: if `TRTC_PUSH_DOMAIN` is empty, return mock URLs so frontend development is not blocked
- Recording URL will be a Tencent VOD URL (not OSS) — format documented in `TRTC_Integration.md` Section 5
- The push URL is RTMP (for OBS Studio); the pull URLs are HLS/FLV (for web players) — inform admin to configure OBS with the push URL
