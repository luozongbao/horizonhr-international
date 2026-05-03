<?php

namespace App\Services;

use App\Jobs\SendInterviewInvitationJob;
use App\Models\Interview;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Str;

class InterviewService
{
    public function __construct(private readonly TrtcService $trtcService) {}
    // ──────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────

    /**
     * Create an interview, generate room_id + room_token, dispatch invitation.
     */
    public function createInterview(User $creator, array $data): Interview
    {
        $student = Student::findOrFail($data['student_id']);

        // Derive enterprise_id for enterprise creators
        $enterpriseId = $data['enterprise_id']
            ?? ($creator->role === 'enterprise' ? $creator->enterprise?->id : null);

        $roomId    = (string) Str::uuid();
        $roomToken = $this->generateRoomToken((int) 0, $student->id); // id not known yet

        $interview = Interview::create([
            'creator_id'    => $creator->id,
            'enterprise_id' => $enterpriseId,
            'student_id'    => $student->id,
            'job_id'        => $data['job_id'] ?? null,
            'title'         => $data['title'],
            'scheduled_at'  => $data['scheduled_at'],
            'duration'      => $data['duration'] ?? 30,
            'room_id'       => $roomId,
            'room_token'    => '', // placeholder; updated below with real interview id
            'status'        => 'scheduled',
        ]);

        // Re-generate token now that we have the real interview id
        $roomToken  = $this->generateRoomToken($interview->id, $student->id);
        $trtcRoomId = $this->trtcService->getRoomId($interview->id);
        $interview->update(['room_token' => $roomToken, 'trtc_room_id' => $trtcRoomId]);

        $joinUrl = $this->buildJoinUrl($roomToken);

        // Notify student asynchronously
        SendInterviewInvitationJob::dispatch($interview, $student, $joinUrl);

        return $interview->fresh();
    }

    /**
     * Cancel an interview, optionally storing a reason.
     */
    public function cancelInterview(Interview $interview, ?string $reason = null): void
    {
        $notes = $reason ?? $interview->notes ?? null;
        $interview->update(['status' => 'cancelled', 'notes' => $notes]);
    }

    /**
     * Return TRTC join config for the interview room.
     *
     * @param  Interview  $interview
     * @param  string     $participantRole     'student' | 'enterprise' | 'admin'
     * @param  int        $participantUserId   User.id of the joining participant
     */
    public function generateJoinConfig(Interview $interview, string $participantRole, int $participantUserId): array
    {
        $trtcUserId = match ($participantRole) {
            'student'    => 'student_' . $participantUserId,
            'enterprise' => 'enterprise_' . $participantUserId,
            default      => 'admin_' . $participantUserId,
        };

        $expire    = (int) config('trtc.expire', 86400);
        $userSig   = $this->trtcService->generateUserSig($trtcUserId, $expire);
        $expiresAt = now()->addSeconds($expire)->toIso8601String();

        return [
            'sdk_app_id' => (int) config('trtc.sdk_app_id', 0),
            'room_id'    => (int) $interview->trtc_room_id,
            'user_id'    => $trtcUserId,
            'user_sig'   => $userSig,
            'expires_at' => $expiresAt,
        ];
    }

    /**
     * Verify a room_token JWT (HS256) signed with APP_KEY.
     * Returns decoded payload array.
     *
     * @throws \RuntimeException on invalid / expired token
     */
    public function verifyRoomToken(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw new \RuntimeException('Invalid room token format.');
        }

        [$headerB64, $payloadB64, $sigB64] = $parts;

        $expectedSig = $this->base64UrlEncode(
            hash_hmac('sha256', "{$headerB64}.{$payloadB64}", config('app.key'), true)
        );

        if (!hash_equals($expectedSig, $sigB64)) {
            throw new \RuntimeException('Room token signature is invalid.');
        }

        $payload = json_decode($this->base64UrlDecode($payloadB64), true);
        if (!$payload || !isset($payload['exp'])) {
            throw new \RuntimeException('Room token payload is malformed.');
        }

        if ($payload['exp'] < time()) {
            throw new \RuntimeException('Room token has expired.');
        }

        return $payload;
    }

    // ──────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────

    private function generateRoomToken(int $interviewId, int $studentId): string
    {
        $header  = $this->base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'interview_id' => $interviewId,
            'student_id'   => $studentId,
            'iat'          => time(),
            'exp'          => time() + 172_800, // +48 h
        ]));
        $sig = $this->base64UrlEncode(
            hash_hmac('sha256', "{$header}.{$payload}", config('app.key'), true)
        );
        return "{$header}.{$payload}.{$sig}";
    }

    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    private function buildJoinUrl(string $roomToken): string
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        return $frontendUrl . '/interview/join?room=' . urlencode($roomToken);
    }
}
