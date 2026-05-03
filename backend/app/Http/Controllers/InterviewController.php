<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInterviewRequest;
use App\Http\Requests\UpdateInterviewRequest;
use App\Models\Interview;
use App\Models\InterviewRecord;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function __construct(private readonly InterviewService $interviewService) {}

    // ──────────────────────────────────────────────────────────────────
    // index — role-filtered listing
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/interviews
     * - admin:      all interviews
     * - enterprise: own enterprise's interviews
     * - student:    interviews where student_id = own student
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Interview::with([
            'student:id,name,avatar',
            'enterprise:id,company_name,logo',
            'job:id,title',
        ])->orderBy('scheduled_at', 'asc');

        switch ($user->role) {
            case 'admin':
                break; // no filter
            case 'enterprise':
                $query->where('enterprise_id', $user->enterprise?->id);
                break;
            case 'student':
                $query->where('student_id', $user->student?->id);
                break;
            default:
                return $this->forbidden();
        }

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
    // show
    // ──────────────────────────────────────────────────────────────────

    /**
     * GET /api/interviews/{id}
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $interview = Interview::with([
            'student:id,name,avatar,nationality,bio',
            'enterprise:id,company_name,logo',
            'job:id,title',
            'records',
        ])->findOrFail($id);

        if (!$this->canAccessInterview($request->user(), $interview)) {
            return $this->forbidden();
        }

        return response()->json(['success' => true, 'data' => $interview]);
    }

    // ──────────────────────────────────────────────────────────────────
    // store — admin or enterprise only
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/interviews
     */
    public function store(StoreInterviewRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!in_array($user->role, ['admin', 'enterprise'], true)) {
            return $this->forbidden();
        }

        $interview = $this->interviewService->createInterview($user, $request->validated());

        return response()->json(['success' => true, 'data' => $interview], 201);
    }

    // ──────────────────────────────────────────────────────────────────
    // update — admin or enterprise only
    // ──────────────────────────────────────────────────────────────────

    /**
     * PUT /api/interviews/{id}
     */
    public function update(UpdateInterviewRequest $request, int $id): JsonResponse
    {
        $user      = $request->user();
        $interview = Interview::findOrFail($id);

        if (!$this->canManageInterview($user, $interview)) {
            return $this->forbidden();
        }

        if ($interview->status !== 'scheduled') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'Only scheduled interviews can be updated.'],
            ], 422);
        }

        $interview->update($request->validated());

        return response()->json(['success' => true, 'data' => $interview->fresh()]);
    }

    // ──────────────────────────────────────────────────────────────────
    // cancel
    // ──────────────────────────────────────────────────────────────────

    /**
     * PUT /api/interviews/{id}/cancel
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        $user      = $request->user();
        $interview = Interview::findOrFail($id);

        if (!$this->canManageInterview($user, $interview)) {
            return $this->forbidden();
        }

        if (!in_array($interview->status, ['scheduled', 'in_progress'], true)) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'This interview cannot be cancelled.'],
            ], 422);
        }

        $reason = $request->input('reason');
        $this->interviewService->cancelInterview($interview, $reason);

        return response()->json(['success' => true, 'data' => $interview->fresh()]);
    }

    // ──────────────────────────────────────────────────────────────────
    // join — accepts Bearer token OR X-Room-Token (via InterviewRoomAuth)
    // ──────────────────────────────────────────────────────────────────

    /**
     * POST /api/interviews/{id}/join
     */
    public function join(Request $request, int $id): JsonResponse
    {
        $interview = Interview::findOrFail($id);

        // Verify interview is joinable
        if (!in_array($interview->status, ['scheduled', 'in_progress'], true)) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INTERVIEW_NOT_JOINABLE', 'message' => 'This interview is not currently active.'],
            ], 422);
        }

        $participantRole   = 'student';
        $participantId     = null;   // model-specific id (Student.id or User.id)
        $participantUserId = null;   // always User.id — used for TRTC user_id

        // Determine auth path and participant role
        if ($request->attributes->get('auth_via_room_token')) {
            $payload = $request->attributes->get('room_token_payload');
            // Verify the token belongs to this interview
            if ((int) ($payload['interview_id'] ?? 0) !== $interview->id) {
                return response()->json([
                    'success' => false,
                    'error'   => ['code' => 'INVALID_ROOM_TOKEN', 'message' => 'Token does not match this interview.'],
                ], 401);
            }
            $participantId     = $payload['student_id'];
            $participantRole   = 'student';
            // Resolve User.id from the interview's student relationship
            $participantUserId = $interview->student?->user_id ?? $participantId;
        } else {
            // Bearer token auth
            $user = $request->user();
            if (!$this->canAccessInterview($user, $interview)) {
                return $this->forbidden();
            }
            $participantId     = $user->id;
            $participantRole   = $user->role;
            $participantUserId = $user->id;
        }

        // Auto-transition scheduled → in_progress when first participant joins
        // (also guards against joining too early via 5-min window)
        if (
            $interview->status === 'scheduled'
            && now()->greaterThanOrEqualTo($interview->scheduled_at->subMinutes(5))
        ) {
            $interview->update(['status' => 'in_progress']);
        }

        // Upsert join record
        InterviewRecord::updateOrCreate(
            ['interview_id' => $interview->id, 'participant_id' => $participantId, 'participant_type' => $participantRole],
            ['joined_at' => now()]
        );

        $config = $this->interviewService->generateJoinConfig(
            $interview->fresh(),
            $participantRole,
            (int) $participantUserId,
        );

        return response()->json(['success' => true, 'data' => $config]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────

    private function canAccessInterview($user, Interview $interview): bool
    {
        if ($user === null) return false;
        return match ($user->role) {
            'admin'      => true,
            'enterprise' => $interview->enterprise_id === $user->enterprise?->id,
            'student'    => $interview->student_id === $user->student?->id,
            default      => false,
        };
    }

    private function canManageInterview($user, Interview $interview): bool
    {
        if ($user === null) return false;
        return match ($user->role) {
            'admin'      => true,
            'enterprise' => $interview->enterprise_id === $user->enterprise?->id,
            default      => false,
        };
    }

    private function forbidden(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error'   => ['code' => 'FORBIDDEN', 'message' => 'You do not have permission to access this interview.'],
        ], 403);
    }
}
