<?php

namespace App\Http\Controllers\Public;

use App\Events\SeminarDanmuReceived;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSeminarRequest;
use App\Http\Requests\SendDanmuRequest;
use App\Models\Seminar;
use App\Models\SeminarMessage;
use App\Models\SeminarRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class SeminarController extends Controller
{
    // ──────────────────────────────────────────────────────────────────
    // index
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/public/seminars
     * Paginated list of seminars. Filters: status, target_audience.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Seminar::withCount('registrations')
            ->orderBy('starts_at', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('target_audience')) {
            $query->where(function ($q) use ($request) {
                $q->where('target_audience', $request->query('target_audience'))
                  ->orWhere('target_audience', 'both');
            });
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
    // show
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/public/seminars/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $seminar = Seminar::withCount('registrations')
            ->with('recording')
            ->findOrFail($id);

        $isRegistered = false;
        if ($email = $request->query('email')) {
            $isRegistered = $seminar->registrations()
                ->where('email', $email)
                ->exists();
        }

        return response()->json([
            'success' => true,
            'data'    => array_merge($seminar->toArray(), ['is_registered' => $isRegistered]),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // register
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/public/seminars/{id}/register
     * Open registration (no auth required). Upserts on duplicate email.
     */
    public function register(RegisterSeminarRequest $request, int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if ($seminar->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'SEMINAR_NOT_OPEN', 'message' => 'Registration is only available before the seminar starts.'],
            ], 422);
        }

        $data = $request->validated();

        // Upsert — no duplicate sends on re-registration
        SeminarRegistration::updateOrCreate(
            ['seminar_id' => $seminar->id, 'email' => $data['email']],
            [
                'name'         => $data['name'],
                'phone'        => $data['phone'] ?? null,
                'user_id'      => $request->user()?->id,
                'reminder_sent'=> false,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'You have been registered for this seminar.',
        ], 201);
    }

    // ──────────────────────────────────────────────────────────────────
    // recording
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/public/seminars/{id}/recording
     */
    public function recording(Request $request, int $id): JsonResponse
    {
        $seminar = Seminar::with('recording')->findOrFail($id);

        if ($seminar->status !== 'ended') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'RECORDING_UNAVAILABLE', 'message' => 'Recording is only available after the seminar ends.'],
            ], 404);
        }

        if (!$seminar->recording) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'RECORDING_NOT_READY', 'message' => 'Recording has not been processed yet.'],
            ], 404);
        }

        // permission=registered: require email in registrations
        if ($seminar->permission === 'registered') {
            $email = $request->query('email') ?? $request->user()?->email;
            if (!$email || !$seminar->registrations()->where('email', $email)->exists()) {
                return response()->json([
                    'success' => false,
                    'error'   => ['code' => 'REGISTRATION_REQUIRED', 'message' => 'You must be registered to access this recording.'],
                ], 403);
            }
        }

        $recording = $seminar->recording;
        $recording->increment('view_count');

        return response()->json([
            'success' => true,
            'data'    => [
                'video_url'       => $recording->video_url,
                'thumbnail_url'   => $recording->thumbnail_url,
                'duration_sec'    => $recording->duration_sec,
                'playback_speeds' => $recording->playback_speeds ?? ['0.5x', '0.75x', '1x', '1.25x', '1.5x', '1.75x', '2x'],
                'default_speed'   => $recording->default_speed  ?? '1x',
                'view_count'      => $recording->view_count,
            ],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // getDanmu
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/public/seminars/{id}/danmu
     * Return last 100 messages ordered by send_at.
     */
    public function getDanmu(int $id): JsonResponse
    {
        Seminar::findOrFail($id); // 404 guard

        $messages = SeminarMessage::where('seminar_id', $id)
            ->orderBy('send_at', 'asc')
            ->latest('id')
            ->limit(100)
            ->get();

        return response()->json(['success' => true, 'data' => $messages]);
    }

    // ──────────────────────────────────────────────────────────────────
    // sendDanmu
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/public/seminars/{id}/danmu
     * Store + broadcast danmu. Rate limited: 10/min per user or IP.
     */
    public function sendDanmu(SendDanmuRequest $request, int $id): JsonResponse
    {
        $seminar = Seminar::findOrFail($id);

        if ($seminar->status !== 'live') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'SEMINAR_NOT_LIVE', 'message' => 'Danmu is only available during a live seminar.'],
            ], 422);
        }

        // Rate limit: 10 per minute per user/IP
        $user    = $request->user();
        $rateKey = 'danmu_rate:' . $id . ':' . ($user ? 'u' . $user->id : $request->ip());

        $count = Redis::incr($rateKey);
        if ($count === 1) {
            Redis::expire($rateKey, 60);
        }
        if ($count > 10) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'RATE_LIMITED', 'message' => 'You are sending danmu too fast. Please wait a moment.'],
            ], 429);
        }

        $validated = $request->validated();

        $message = SeminarMessage::create([
            'seminar_id' => $seminar->id,
            'user_id'    => $user?->id,
            'user_name'  => $user?->name ?? 'Anonymous',
            'content'    => $validated['content'],
            'color'      => $validated['color']    ?? '#FFFFFF',
            'position'   => $validated['position'] ?? 'scroll',
            'send_at'    => now(),
        ]);

        // Broadcast to all viewers subscribed to the seminar channel
        broadcast(new SeminarDanmuReceived($message));

        return response()->json(['success' => true, 'data' => $message], 201);
    }

    // ──────────────────────────────────────────────────────────────────
    // watch
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/public/seminars/{id}/watch
     *
     * Returns live pull URLs while the seminar is live, or the recording URL
     * once it has been processed. Requires the seminar to be live or ended.
     */
    public function watch(int $id): JsonResponse
    {
        $seminar = Seminar::with('recording')->findOrFail($id);

        if ($seminar->status === 'live') {
            $pullUrls = app(\App\Services\TrtcLiveService::class)
                ->getPullUrls($seminar->stream_key ?? '');

            return response()->json([
                'success' => true,
                'data'    => [
                    'type'      => 'live',
                    'pull_urls' => $pullUrls,
                ],
            ]);
        }

        if ($seminar->status === 'ended') {
            if (!$seminar->recording) {
                return response()->json([
                    'success' => false,
                    'error'   => ['code' => 'RECORDING_NOT_READY', 'message' => 'The recording is still being processed. Please check back shortly.'],
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data'    => [
                    'type'        => 'recording',
                    'video_url'   => $seminar->recording->video_url,
                    'duration_sec'=> $seminar->recording->duration_sec,
                ],
            ]);
        }

        // status = 'scheduled' or 'cancelled'
        return response()->json([
            'success' => false,
            'error'   => ['code' => 'SEMINAR_NOT_STARTED', 'message' => 'The seminar has not started yet.'],
        ], 404);
    }
}
