<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSeminarRequest;
use App\Http\Requests\Admin\UpdateSeminarRequest;
use App\Models\Seminar;
use App\Services\TrtcLiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeminarController extends Controller
{
    public function __construct(private readonly TrtcLiveService $trtcLive) {}

    // ──────────────────────────────────────────────────────────────────
    // index
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/seminars
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Seminar::withCount('registrations')
            ->orderBy('starts_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginated->items(),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // store
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/admin/seminars
     */
    public function store(StoreSeminarRequest $request): JsonResponse
    {
        $seminar = Seminar::create(array_merge(
            $request->validated(),
            ['status' => 'scheduled', 'reminder_sent' => false]
        ));

        // Generate and persist the stream key immediately so it never changes
        $streamKey = $this->trtcLive->generateStreamKey($seminar->id);
        $seminar->update(['stream_key' => $streamKey]);

        return response()->json(['success' => true, 'data' => $seminar->fresh()], 201);
    }

    // ──────────────────────────────────────────────────────────────────
    // show
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/seminars/{id}
     */
    public function show(int $id): JsonResponse
    {
        $seminar = Seminar::withCount('registrations')->with('recording')->findOrFail($id);

        return response()->json(['success' => true, 'data' => $seminar]);
    }

    // ──────────────────────────────────────────────────────────────────
    // update
    // ──────────────────────────────────────────────────────────────────

    /**
     * PUT /api/admin/seminars/{id}
     */
    public function update(UpdateSeminarRequest $request, int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);
        $seminar->update($request->validated());

        return response()->json(['success' => true, 'data' => $seminar->fresh()]);
    }

    // ──────────────────────────────────────────────────────────────────
    // destroy
    // ──────────────────────────────────────────────────────────────────

    /**
     * DELETE /api/admin/seminars/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if (in_array($seminar->status, ['live', 'in_progress'], true)) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'SEMINAR_ACTIVE', 'message' => 'Cannot delete a seminar that is currently live.'],
            ], 422);
        }

        $seminar->delete();

        return response()->json(['success' => true, 'message' => 'Seminar deleted.']);
    }

    // ──────────────────────────────────────────────────────────────────
    // registrations
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/admin/seminars/{id}/registrations
     */
    public function registrations(Request $request, int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);
        $perPage = min((int) ($request->query('per_page', 50)), 200);

        $paginated = $seminar->registrations()->orderBy('registered_at', 'asc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginated->items(),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // goLive
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/admin/seminars/{id}/go-live
     * Set status=live and return fresh RTMP push + pull URLs.
     */
    public function goLive(int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if ($seminar->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'Only scheduled seminars can go live.'],
            ], 422);
        }

        // Ensure stream key exists (backfill in case it was created before TASK-019)
        if (empty($seminar->stream_key)) {
            $seminar->update(['stream_key' => $this->trtcLive->generateStreamKey($seminar->id)]);
            $seminar = $seminar->fresh();
        }

        $streamKey = $seminar->stream_key;
        $pushUrl   = $this->trtcLive->getPushUrl($streamKey);
        $pullUrls  = $this->trtcLive->getPullUrls($streamKey);

        $seminar->update([
            'status'     => 'live',
            'stream_url' => $pullUrls['hls'],
        ]);

        return response()->json([
            'success' => true,
            'data'    => [
                'seminar'   => $seminar->fresh(),
                'push_url'  => $pushUrl,          // RTMP URL for OBS — admin-only
                'pull_urls' => $pullUrls,          // HLS/FLV/RTMP for viewers
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // endLive
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/admin/seminars/{id}/end-live
     * Set status=ended. Recording arrives via webhook callback (POST /api/webhooks/trtc-live).
     */
    public function endLive(int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if ($seminar->status !== 'live') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'Only live seminars can be ended.'],
            ], 422);
        }

        $seminar->update([
            'status'   => 'ended',
            'ended_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data'    => $seminar->fresh(),
            'message' => 'Seminar ended. Recording will be available once Tencent CSS processing is complete.',
        ]);
    }
}
