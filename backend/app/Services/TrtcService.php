<?php

namespace App\Services;

/**
 * Tencent RTC (TRTC) service.
 *
 * Implements the official TLSSigAPIv2 UserSig algorithm:
 *   1. Build the "content-to-be-signed" string from identifier, sdkappid, time, expire.
 *   2. HMAC-SHA256 sign with secretKey → base64-encode → TLS.sig.
 *   3. Build JSON payload containing all TLS.* fields including TLS.sig.
 *   4. gzcompress the JSON, base64-encode with Tencent variant (+→*, /→-, =→_).
 *
 * Reference: DOCUMENTS/TRTC_Integration.md  §3 (UserSig algorithm)
 */
class TrtcService
{
    private int    $sdkAppId;
    private string $secretKey;

    public function __construct()
    {
        $this->sdkAppId  = (int)    config('trtc.sdk_app_id', 0);
        $this->secretKey = (string) config('trtc.secret_key', '');
    }

    // ──────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────

    /**
     * Generate a TRTC UserSig for the given userId.
     *
     * @param  string  $userId  TRTC user identifier (e.g. "student_42")
     * @param  int     $expire  Token lifetime in seconds (default: from config, 24 h)
     * @return string           Base64-url encoded UserSig (Tencent variant)
     */
    public function generateUserSig(string $userId, ?int $expire = null): string
    {
        $expire ??= (int) config('trtc.expire', 86400);

        // Dev/test mode: no credentials configured → return a recognisable mock value
        // so frontend can still be developed without real TRTC keys.
        if (empty($this->sdkAppId) || empty($this->secretKey)) {
            return 'mock_usersig_no_trtc_credentials';
        }

        $now = time();
        $sig = $this->hmacSha256($userId, $now, $expire);

        $tokenJson = json_encode([
            'TLS.ver'        => '2.0',
            'TLS.identifier' => $userId,
            'TLS.sdkappid'   => $this->sdkAppId,
            'TLS.time'       => $now,
            'TLS.expire'     => $expire,
            'TLS.sig'        => $sig,
        ], JSON_UNESCAPED_UNICODE);

        $compressed = gzcompress($tokenJson);
        $b64        = base64_encode($compressed);

        // Tencent's custom base64 variant: +→*, /→-, =→_
        return str_replace(['+', '/', '='], ['*', '-', '_'], $b64);
    }

    /**
     * Derive a numeric TRTC room ID from the interview's DB id.
     *
     * Uses the formula from TASK-018:
     *   (int)(microtime(true) * 1000) % 1_000_000_000 + $interviewId
     *
     * The result is always a valid uint32 value assuming interviewId < 3.3 billion.
     *
     * @param  int  $interviewId  interviews.id primary key
     * @return int                Numeric TRTC room ID
     */
    public function getRoomId(int $interviewId): int
    {
        return (int)(microtime(true) * 1000) % 1_000_000_000 + $interviewId;
    }

    // ──────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Build the HMAC-SHA256 signature as required by TLSSigAPIv2.
     *
     * Content format (each field on its own line, terminated with \n):
     *   TLS.identifier:{userId}
     *   TLS.sdkappid:{sdkAppId}
     *   TLS.time:{now}
     *   TLS.expire:{expire}
     */
    private function hmacSha256(string $userId, int $now, int $expire): string
    {
        $content = "TLS.identifier:{$userId}\n"
                 . "TLS.sdkappid:{$this->sdkAppId}\n"
                 . "TLS.time:{$now}\n"
                 . "TLS.expire:{$expire}\n";

        return base64_encode(hash_hmac('sha256', $content, $this->secretKey, true));
    }
}
