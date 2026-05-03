<?php

namespace App\Services;

use App\Models\Seminar;
use Illuminate\Support\Str;

/**
 * Tencent CSS (Cloud Streaming Services) live-streaming service.
 *
 * Handles RTMP push URL and HLS/FLV/RTMP pull URL generation for seminars.
 * Authentication uses Tencent's txSecret algorithm:
 *   txSecret = MD5(authKey + streamId + txTime)
 *   txTime   = strtoupper(dechex(expire_unix_timestamp))
 *
 * Reference: DOCUMENTS/TRTC_Integration.md §4–5
 */
class TrtcLiveService
{
    // ──────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────

    /**
     * Generate a unique stream key for a seminar.
     * Format: seminar_{seminarId}_{random8chars}
     */
    public function generateStreamKey(int $seminarId): string
    {
        return 'seminar_' . $seminarId . '_' . Str::lower(Str::random(8));
    }

    /**
     * Generate authenticated RTMP push URL for OBS/encoder.
     *
     * @param  string  $streamKey     Stream key (e.g. "seminar_42_abc12345")
     * @param  int     $expireSeconds URL validity window in seconds (default: 2 h)
     * @return string                 Signed RTMP push URL
     */
    public function getPushUrl(string $streamKey, int $expireSeconds = 7200): string
    {
        $pushDomain = config('trtc.push_domain', '');
        $appName    = config('trtc.app_name', 'live');
        $pushKey    = config('trtc.push_key', '');

        if (empty($pushDomain) || empty($pushKey)) {
            return 'rtmp://mock-push.example.com/' . $appName . '/' . $streamKey . '?MOCK=no_credentials';
        }

        $expireAt  = time() + $expireSeconds;
        $txTime    = strtoupper(dechex($expireAt));
        $txSecret  = $this->generateTxSecret($streamKey, $expireAt, $pushKey);

        return "rtmp://{$pushDomain}/{$appName}/{$streamKey}?txSecret={$txSecret}&txTime={$txTime}";
    }

    /**
     * Generate pull URLs for viewers (HLS, FLV, RTMP).
     *
     * @param  string  $streamKey     Stream key
     * @param  int     $expireSeconds URL validity window (default: 4 h for viewers)
     * @return array{hls: string, flv: string, rtmp: string}
     */
    public function getPullUrls(string $streamKey, int $expireSeconds = 14400): array
    {
        $playDomain = config('trtc.play_domain', '');
        $appName    = config('trtc.app_name', 'live');
        $playKey    = config('trtc.play_key', '');

        if (empty($playDomain) || empty($playKey)) {
            return [
                'hls'  => 'https://mock-play.example.com/' . $appName . '/' . $streamKey . '.m3u8?MOCK=no_credentials',
                'flv'  => 'https://mock-play.example.com/' . $appName . '/' . $streamKey . '.flv?MOCK=no_credentials',
                'rtmp' => 'rtmp://mock-play.example.com/' . $appName . '/' . $streamKey . '?MOCK=no_credentials',
            ];
        }

        $expireAt = time() + $expireSeconds;
        $txTime   = strtoupper(dechex($expireAt));
        $txSecret = $this->generateTxSecret($streamKey, $expireAt, $playKey);
        $auth     = "txSecret={$txSecret}&txTime={$txTime}";

        return [
            'hls'  => "https://{$playDomain}/{$appName}/{$streamKey}.m3u8?{$auth}",
            'flv'  => "https://{$playDomain}/{$appName}/{$streamKey}.flv?{$auth}",
            'rtmp' => "rtmp://{$playDomain}/{$appName}/{$streamKey}?{$auth}",
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────────────────

    /**
     * Compute Tencent txSecret:
     *   txSecret = MD5(authKey + streamId + txTime_hex_uppercase)
     *
     * @param  string  $streamKey   Stream identifier
     * @param  int     $expireTime  Unix timestamp of expiry
     * @param  string  $authKey     Push or play authentication key
     * @return string               Lowercase hex MD5 string
     */
    private function generateTxSecret(string $streamKey, int $expireTime, string $authKey): string
    {
        $txTime = strtoupper(dechex($expireTime));
        return md5($authKey . $streamKey . $txTime);
    }
}
