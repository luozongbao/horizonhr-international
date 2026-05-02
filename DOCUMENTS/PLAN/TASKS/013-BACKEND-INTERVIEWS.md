# TASK-013: Backend Interview Module

**Phase:** 4 — Backend: Core Business Features  
**Status:** Pending  
**Depends On:** TASK-012  
**Priority:** HIGH  

---

## Objective

Implement interview scheduling, management, and room joining. Interviews are created by admins or enterprises, scheduled with a specific student, and the student joins via a secure link (without needing to be logged in). WebRTC room config is provisioned by TASK-018 (TRTC); this task handles the API layer, scheduling, and room token issuance.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 3.10 (Interviews)
2. `DOCUMENTS/DESIGNS/SYSTEM_DESIGN.md` — Sections 1.2.12–1.2.13, 2.3.6
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.B.2 (Student: interviews), IV.B.3 (Enterprise: interviews), IV.C.2 (Online Video Interview)

---

## API Endpoints to Implement

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| GET | `/api/interviews` | Admin/Enterprise/Student | List own interviews |
| POST | `/api/interviews` | Admin/Enterprise | Create interview |
| GET | `/api/interviews/{id}` | Admin/Enterprise/Student | Interview detail |
| PUT | `/api/interviews/{id}` | Admin/Enterprise | Update interview |
| PUT | `/api/interviews/{id}/cancel` | Admin/Enterprise | Cancel interview |
| POST | `/api/interviews/{id}/join` | Bearer OR Room Token | Join interview room |
| PUT | `/api/admin/interviews/{id}/record` | Admin | Update interview record (notes, result) |

---

## Deliverables

### Controllers
- `app/Http/Controllers/InterviewController.php` (shared, role-filtered)
  - `index(Request $request)` — list interviews based on role:
    - admin: all interviews
    - enterprise: interviews where enterprise_id = own enterprise
    - student: interviews where student_id = own student
  - `show(int $id)` — detail; verify permission
  - `store(StoreInterviewRequest $request)` — admin or enterprise only; create interview row, generate `room_id` (UUID), generate `room_token` (signed token for student join link); dispatch invitation email
  - `update(UpdateInterviewRequest $request, int $id)` — update title, scheduled_at, duration
  - `cancel(Request $request, int $id)` — set status=cancelled; optional reason in notes; dispatch cancellation notification
  - `join(Request $request, int $id)` — validate auth (Bearer token OR room_token header); return TRTC config + room info

### Middleware / Auth
- `app/Http/Middleware/InterviewRoomAuth.php` — accept either:
  1. Standard `Authorization: Bearer {sanctum_token}` (admin/enterprise/logged-in student)
  2. `X-Room-Token: {room_token}` header (student joining via link without login)

### Services
- `app/Services/InterviewService.php`
  - `createInterview(User $creator, array $data): Interview`
    - Create interview row with status=scheduled
    - Generate room_id = `UUID4`
    - Generate room_token = `JWT` signed with `APP_KEY`, payload: `{interview_id, student_id, expires_at: +48h}`
    - Dispatch `SendInterviewInvitationJob`
  - `generateJoinConfig(Interview $interview, string $participantRole): array`
    - Returns TRTC UserSig + room_id + other config (TASK-018 implements TRTC UserSig)
    - Stub response until TASK-018: `{ room_id: '...', trtc_user_sig: 'stub', sdk_app_id: 0 }`
  - `cancelInterview(Interview $interview, ?string $reason): void`

### Jobs / Mail
- `app/Jobs/SendInterviewInvitationJob.php` — sends invitation email to student with join link; uses `email-templates/interview-invitation.html`
- `app/Jobs/SendInterviewResultJob.php` — sends result notification to student; uses `email-templates/interview-result.html`

### Routes
```php
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/interviews', [InterviewController::class, 'index']);
    Route::post('/interviews', [InterviewController::class, 'store']);
    Route::get('/interviews/{id}', [InterviewController::class, 'show']);
    Route::put('/interviews/{id}', [InterviewController::class, 'update']);
    Route::put('/interviews/{id}/cancel', [InterviewController::class, 'cancel']);
    Route::put('/admin/interviews/{id}/record', [AdminInterviewController::class, 'updateRecord']);
});
// Join endpoint: accepts Bearer OR Room Token
Route::post('/interviews/{id}/join', [InterviewController::class, 'join'])
    ->middleware('interview.room.auth');
```

---

## Interview Invitation Flow (from REQ IV.C.2)

1. Admin or enterprise creates interview: `POST /api/interviews`
2. System generates:
   - `room_id` — unique room identifier
   - `room_token` — signed token for student join link
3. Student receives email with link: `https://app.horizonhr.com/interview/join?token={room_token}`
4. Student clicks link → frontend calls `POST /api/interviews/{id}/join` with `X-Room-Token` header
5. Backend validates token → returns TRTC config
6. Frontend initializes TRTC client with config

---

## Join Endpoint Logic

```
POST /api/interviews/{id}/join
Headers: Authorization: Bearer {token} OR X-Room-Token: {room_token}

Logic:
1. If Bearer token: verify Sanctum auth, check user is admin/enterprise/student of this interview
2. If X-Room-Token: decode JWT, verify interview_id matches, check not expired
3. Verify interview.status = 'scheduled' or 'in_progress'
4. If status = 'scheduled' and now >= scheduled_at - 5min: set status = 'in_progress'
5. Determine participant_role from auth type
6. Create/update interview_records row (joined_at = now)
7. Return: { room_id, trtc_config: { sdk_app_id, user_sig, ... }, websocket_url }
```

---

## Interview Record Update (Admin)

`PUT /api/admin/interviews/{id}/record`:
- notes: text
- result: pass|fail|pending|no_show
- rating: 1-5

If result updated, dispatch `SendInterviewResultJob` to notify student.

---

## Interview Status Flow

```
scheduled → in_progress → completed
          ↘ cancelled
                        ↘ no_show
```

---

## Acceptance Criteria

- [ ] `POST /api/interviews` (enterprise) creates interview with room_id and room_token
- [ ] Interview invitation email job dispatched on creation
- [ ] `PUT /api/interviews/{id}/cancel` sets status=cancelled
- [ ] `POST /api/interviews/{id}/join` with valid Bearer token returns join config
- [ ] `POST /api/interviews/{id}/join` with valid X-Room-Token header returns join config (student without login)
- [ ] Expired room_token returns 401
- [ ] Student cannot join an interview they are not invited to
- [ ] Enterprise cannot see/join interviews from other enterprises
- [ ] Admin can see and manage all interviews
- [ ] `PUT /api/admin/interviews/{id}/record` saves notes and result
- [ ] Result update dispatches interview result email

---

## Notes

- TRTC UserSig generation implemented in TASK-018; for now return stub config
- Room token JWT should be signed with `APP_KEY`; use `tymon/jwt-auth` or PHP's `firebase/php-jwt`
- The `room_token` stored in `interviews.room_token` should be the raw JWT (needed to validate X-Room-Token header)
- Interview join link format: `{FRONTEND_URL}/interview/join?room={room_token}`
