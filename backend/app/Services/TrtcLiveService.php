<?php

namespace App\Services;

use App\Models\Seminar;

/**
 * Stub service for Tencent CSS / TRTC CDN live-streaming integration.
 *
 * TASK-019: Replace stub methods with real Tencent CSS API calls.
 * Docs: https://www.tencentcloud.com/document/product/267
 */
class TrtcLiveService
{
    /**
     * Generate RTMP push URL and playback URL for a seminar.
     *
     * TASK-019: Call Tencent CSS CreateLivePushStream API, derive stream URLs.
     * Returns stub config until TASK-019 is implemented.
     *
     * @param  Seminar  $seminar
     * @return array{push_url: string, stream_url: string, stream_key: string}
     */
    public function getStreamConfig(Seminar $seminar): array
    {
        // TASK-019: Replace with real Tencent CSS push URL generation.
        // Expected implementation:
        //   $appId    = config('services.tencent.css.app_id');
        //   $domain   = config('services.tencent.css.push_domain');
        //   $streamId = 'seminar-' . $seminar->id;
        //   $txTime   = dechex(time() + 7200); // 2h validity
        //   $txSecret = md5(config('services.tencent.css.push_key') . $streamId . $txTime);
        //   $pushUrl  = "rtmp://{$domain}/live/{$streamId}?txSecret={$txSecret}&txTime={$txTime}";
        //   $pullUrl  = "https://{$domain}/live/{$streamId}.m3u8";

        return [
            'push_url'   => 'rtmp://stub-push.example.com/live/seminar-' . $seminar->id . '?TASK-019',
            'stream_url' => 'https://stub-cdn.example.com/live/seminar-' . $seminar->id . '.m3u8?TASK-019',
            'stream_key' => 'stub_stream_key_TASK-019',
        ];
    }

    /**
     * End the live stream and trigger cloud recording.
     *
     * TASK-019: Call Tencent CSS ForbidLivePushStream API; start cloud recording via TRTC API.
     * Recording completion triggers a webhook callback from Tencent CSS — handle in TASK-019.
     *
     * @param  Seminar  $seminar
     * @return void
     */
    public function endStream(Seminar $seminar): void
    {
        // TASK-019: Forbid push stream + start recording
        // config('services.tencent.css.*') credentials needed.
    }
}
