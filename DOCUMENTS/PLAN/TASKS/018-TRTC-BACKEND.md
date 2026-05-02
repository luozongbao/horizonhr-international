# TASK-018: TRTC Backend Integration

**Phase:** 5 — External Service Integrations (Backend)  
**Status:** Pending  
**Depends On:** TASK-013  
**Priority:** HIGH  

---

## Objective

Implement the Tencent RTC (TRTC) backend integration: UserSig token generation for authenticated room entry, server-side room management, and replacing all TRTC-related stubs left in TASK-013 with working implementations.

---

## Reference Documents

1. `DOCUMENTS/TRTC_Integration.md` — Full TRTC integration guide (PRIMARY)
2. `DOCUMENTS/DESIGNS/TRTC_Integration.md` — Same doc (check both paths)
3. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.10 (Interviews: join endpoint)
4. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Section 2.3.5 (Interview Module)
5. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.D.2 (Video Interview: TRTC/WebRTC)

---

## TRTC Concepts (from TRTC_Integration.md)

| Concept | Description |
|---------|-------------|
| `SDKAppID` | Tencent project identifier |
| `UserSig` | HMAC-SHA256 signed token — proves user identity to TRTC server |
| `RoomID` | Numeric or string room identifier |
| `UserID` | String identifier for TRTC user (must be unique per user) |
| Token expiry | Recommend 24 hours for interview sessions |

---

## Deliverables

### Service
- `app/Services/TrtcService.php`
  ```php
  class TrtcService {
      private int $sdkAppId;
      private string $secretKey;
      
      // Generate UserSig for a user to join a TRTC room
      public function generateUserSig(string $userId, int $expire = 86400): string
      
      // Build the TLSSigAPIv2 HMAC-SHA256 signature (per TRTC docs)
      private function genSig(string $userId, int $expire): string
      
      // Return the RoomID for an interview (numeric, from interviews.trtc_room_id)
      public function getRoomId(int $interviewId): int
  }
  ```
  
  UserSig algorithm (from TRTC_Integration.md):
  1. Current timestamp + expiry seconds = expire timestamp
  2. Build JSON string: `{"TLS.ver":"2.0","TLS.identifier":"{userId}","TLS.sdkappid":{sdkAppId},"TLS.expire":{expire},"TLS.time":{now}}`
  3. HMAC-SHA256 sign with `secretKey`
  4. Base64-encode the binary signature
  5. Build final token: `{"TLS.ver":"2.0",...,"TLS.sig":"{base64sig}"}`
  6. Compress with zlib deflate, base64-url encode

### Config
- `config/trtc.php`:
  ```php
  return [
      'sdk_app_id' => env('TRTC_SDK_APP_ID'),
      'secret_key' => env('TRTC_SECRET_KEY'),
  ];
  ```
- Add to `.env.example`:
  ```
  TRTC_SDK_APP_ID=
  TRTC_SECRET_KEY=
  ```

### Update Interview Join Endpoint (TASK-013 stub)
`POST /api/interviews/{id}/join` — currently returns stub response; update to:
1. Validate user is student or enterprise associated with this interview
2. Check interview status is `scheduled` or `ongoing`
3. Set interview status to `ongoing` if first to join
4. Generate `user_id` for TRTC: `"student_{user_id}"` or `"enterprise_{user_id}"`
5. Call `TrtcService::generateUserSig($trtcUserId)`
6. Return:
   ```json
   {
     "success": true,
     "data": {
       "sdk_app_id": 1400000000,
       "room_id": 100123,
       "user_id": "student_42",
       "user_sig": "eJx...",
       "expires_at": "2026-04-15T12:00:00Z"
     }
   }
   ```

### Update Interview Creation (TASK-013 stub)
In `app/Http/Controllers/Enterprise/InterviewController.php::store()`:
- After creating the interview record, assign a unique `trtc_room_id`
- Room ID generation: use `(int)(microtime(true) * 1000) % 1000000000 + $interviewId` to get a 9-digit numeric room ID
- Store in `interviews.trtc_room_id`

---

## TRTC UserID Convention

| User Type | TRTC UserID Format | Example |
|-----------|-------------------|---------|
| Student | `student_{user_id}` | `student_42` |
| Enterprise | `enterprise_{user_id}` | `enterprise_15` |

This ensures no collision between student and enterprise user IDs in the same room.

---

## Acceptance Criteria

- [ ] `TrtcService::generateUserSig()` generates valid UserSig (HMAC-SHA256, zlib, base64-url)
- [ ] UserSig uses correct algorithm matching TRTC documentation
- [ ] `POST /api/interviews/{id}/join` returns `sdk_app_id`, `room_id`, `user_id`, `user_sig`
- [ ] Only the student and enterprise assigned to the interview can join the room
- [ ] Interview status transitions to `ongoing` when first participant joins
- [ ] `trtc_room_id` is assigned on interview creation and persisted
- [ ] `TRTC_SDK_APP_ID` and `TRTC_SECRET_KEY` in `.env.example`
- [ ] Non-participant users receive 403 when trying to join
- [ ] Completed/cancelled interviews return 403 for join attempt

---

## Notes

- Reference `DOCUMENTS/TRTC_Integration.md` Section 3 for exact UserSig algorithm implementation
- The PHP HMAC-SHA256 UserSig can be generated using `hash_hmac('sha256', $content, $key, true)` then base64-encode
- TRTC room IDs must be numeric (uint32, max ~4 billion) for standard rooms
- For development without TRTC credentials: if `TRTC_SDK_APP_ID` is empty, return mock values so frontend can still be developed
- Tencent provides a reference PHP implementation — follow it exactly from TRTC_Integration.md
