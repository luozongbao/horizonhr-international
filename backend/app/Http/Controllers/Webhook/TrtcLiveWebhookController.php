<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Models\SeminarRecording;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Handles Tencent CSS event callback webhooks.
 *
 * Tencent CSS delivers callbacks as HTTP POST with JSON body.
 * Signature verification:
 *   The callback URL should include `sign=MD5(hashKey+t)&t=<unix_ts>` query params.
 *   Reference: https://www.tencentcloud.com/document/product/267/31566
 *
 * Supported event types (event_type field):
 *   0  / PushBegin   → stream started  → mark seminar live
 *   1  / PushEnd     → stream stopped  → mark seminar ended
 *   100/ VodRecord   → recording ready → store recording URL
 */
class TrtcLiveWebhookController extends Controller
{
    // Event type constants from Tencent CSS
    private const EVENT_PUSH_BEGIN = 0;     // Stream push started
    private const EVENT_PUSH_END   = 1;     // Stream push disconnected
    private const EVENT_RECORDING  = 100;   // VOD recording file ready

    /**
     * POST /api/webhooks/trtc-live
     * No authentication middleware — Tencent hits this endpoint directly.
     */
    public function handle(Request $request): JsonResponse
    {
        // ── 1. Verify Tencent callback signature ─────────────────────
        if (!$this->verifySignature($request)) {
            Log::warning('TrtcLiveWebhook: invalid signature', [
                'ip'    => $request->ip(),
                'query' => $request->query(),
            ]);
            return response()->json(['code' => 0, 'message' => 'invalid_signature'], 401);
        }

        // ── 2. Parse payload ─────────────────────────────────────────
        $payload   = $request->json()->all();
        $eventType = (int) ($payload['event_type'] ?? -1);
        $streamId  = $payload['stream_id'] ?? null;

        if (!$streamId) {
            Log::warning('TrtcLiveWebhook: missing stream_id', ['payload' => $payload]);
            return response()->json(['code' => 0]);
        }

        // ── 3. Resolve seminar by stream key ─────────────────────────
        $seminar = Seminar::where('stream_key', $streamId)->first();

        if (!$seminar) {
            Log::info('TrtcLiveWebhook: unknown stream_id, ignoring', ['stream_id' => $streamId]);
            return response()->json(['code' => 0]);
        }

        // ── 4. Dispatch by event type ─────────────────────────────────
        match ($eventType) {
            self::EVENT_PUSH_BEGIN => $this->handlePushBegin($seminar),
            self::EVENT_PUSH_END   => $this->handlePushEnd($seminar),
            self::EVENT_RECORDING  => $this->handleRecording($seminar, $payload),
            default                => Log::info('TrtcLiveWebhook: unhandled event_type', [
                'event_type' => $eventType,
                'stream_id'  => $streamId,
            ]),
        };

        // Tencent expects {"code": 0} to acknowledge receipt
        return response()->json(['code' => 0]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Event handlers
    // ──────────────────────────────────────────────────────────────────

    /** Stream started — mark seminar as live if still scheduled. */
    private function handlePushBegin(Seminar $seminar): void
    {
        if ($seminar->status === 'scheduled') {
            $seminar->update(['status' => 'live']);
            Log::info('TrtcLiveWebhook: seminar marked live', ['seminar_id' => $seminar->id]);
        }
    }

    /** Stream ended — mark seminar as ended if still live. */
    private function handlePushEnd(Seminar $seminar): void
    {
        if ($seminar->status === 'live') {
            $seminar->update([
                'status'   => 'ended',
                'ended_at' => now(),
            ]);
            Log::info('TrtcLiveWebhook: seminar marked ended', ['seminar_id' => $seminar->id]);
        }
    }

    /**
     * Recording file ready — upsert a SeminarRecording entry.
     *
     * Tencent CSS VOD record callback payload (event_type=100) includes:
     *   file_id, file_url, start_time, end_time, duration, stream_id
     */
    private function handleRecording(Seminar $seminar, array $payload): void
    {
        $videoUrl    = $payload['file_url']  ?? null;
        $durationSec = (int) ($payload['duration'] ?? 0);

        if (!$videoUrl) {
            Log::warning('TrtcLiveWebhook: recording event missing file_url', [
                'seminar_id' => $seminar->id,
                'payload'    => $payload,
            ]);
            return;
        }

        SeminarRecording::updateOrCreate(
            ['seminar_id' => $seminar->id],
            [
                'title'       => $seminar->title_en . ' — Recording',
                'video_url'   => $videoUrl,
                'duration_sec'=> $durationSec ?: 1,
                'playback_speeds' => ['0.5x', '0.75x', '1x', '1.25x', '1.5x', '2x'],
                'default_speed'   => '1x',
                'view_count'      => 0,
            ]
        );

        Log::info('TrtcLiveWebhook: recording saved', [
            'seminar_id' => $seminar->id,
            'video_url'  => $videoUrl,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Signature verification
    // ──────────────────────────────────────────────────────────────────

    /**
     * Verify Tencent CSS callback signature.
     *
     * Tencent appends ?sign=MD5(hashKey+t)&t=<unix_ts> to the callback URL.
     * If no callback hash key is configured, skip verification in development.
     */
    private function verifySignature(Request $request): bool
    {
        $hashKey = config('trtc.callback_hash_key', '');

        // Skip verification when no key is configured (local dev / unset env)
        if (empty($hashKey)) {
            return true;
        }

        $sign = $request->query('sign', '');
        $t    = (int) $request->query('t', 0);

        if (!$sign || !$t) {
            return false;
        }

        // Replay-attack guard: reject calls older than 10 minutes
        if (abs(time() - $t) > 600) {
            return false;
        }

        $expected = md5($hashKey . $t);

        return hash_equals($expected, strtolower($sign));
    }
}
