<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Seminar;
use App\Services\TrtcLiveService;
use Illuminate\Http\JsonResponse;

class SeminarLiveController extends Controller
{
    public function __construct(private readonly TrtcLiveService $trtcLive) {}

    /**
     * GET /api/admin/seminars/{id}/live-urls
     *
     * Returns fresh push URL (for OBS) and pull URLs (for viewers).
     * The txSecret tokens are regenerated on every call — do NOT cache.
     */
    public function getLiveUrls(int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if (empty($seminar->stream_key)) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'NO_STREAM_KEY', 'message' => 'This seminar has no stream key. Re-create the seminar to generate one.'],
            ], 422);
        }

        $streamKey = $seminar->stream_key;

        return response()->json([
            'success' => true,
            'data'    => [
                'seminar_id' => $seminar->id,
                'stream_key' => $streamKey,
                'status'     => $seminar->status,
                'push_url'   => $this->trtcLive->getPushUrl($streamKey),
                'pull_urls'  => $this->trtcLive->getPullUrls($streamKey),
                'note'       => 'URLs contain time-limited txSecret tokens. Refresh this endpoint to get new tokens.',
            ],
        ]);
    }
}
