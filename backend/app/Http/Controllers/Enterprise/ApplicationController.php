<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprise\UpdateApplicationStatusRequest;
use App\Jobs\SendApplicationStatusChangedJob;
use App\Models\Application;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function __construct(private readonly ResumeService $resumeService) {}

    /**
     * GET /api/enterprise/applications
     * Applications for own jobs; filter by job_id, status.
     */
    public function index(Request $request): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $perPage    = min((int) ($request->query('per_page', 20)), 100);

        $query = Application::with([
                'job:id,title,location,job_type',
                'student:id,name,nationality,avatar',
                'resume:id,file_name,file_type',
            ])
            ->whereHas('job', fn ($q) => $q->where('enterprise_id', $enterprise->id))
            ->orderBy('applied_at', 'desc');

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->query('job_id'));
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

    /**
     * GET /api/enterprise/applications/{id}
     * Full application detail: student profile, resume download URL, talent card.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $enterprise  = $request->user()->enterprise;
        $application = Application::with([
                'job:id,title,location,job_type,enterprise_id',
                'student.talentCard',
                'resume',
            ])
            ->whereHas('job', fn ($q) => $q->where('enterprise_id', $enterprise->id))
            ->findOrFail($id);

        $student    = $application->student;
        $resume     = $application->resume;
        $talentCard = $student?->talentCard;

        // TASK-016: replace with presigned OSS URL
        $resumeUrl = $resume ? $this->resumeService->fileUrl($resume) : null;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $application->id,
                'status'       => $application->status,
                'applied_at'   => $application->applied_at,
                'cover_letter' => $application->cover_letter,
                'notes'        => $application->notes,
                'job'          => $application->job,
                'student'      => $student ? [
                    'id'          => $student->id,
                    'name'        => $student->name,
                    'nationality' => $student->nationality,
                    'bio'         => $student->bio,
                    'avatar'      => $student->avatar,
                ] : null,
                'resume'       => $resume ? [
                    'id'        => $resume->id,
                    'file_name' => $resume->file_name,
                    'file_type' => $resume->file_type,
                    'file_size' => $resume->file_size,
                    'url'       => $resumeUrl,
                ] : null,
                'talent_card'  => ($talentCard && in_array($talentCard->status, ['visible', 'featured'], true))
                    ? $talentCard
                    : null,
            ],
        ]);
    }

    /**
     * PUT /api/enterprise/applications/{id}/status
     */
    public function updateStatus(UpdateApplicationStatusRequest $request, int $id): JsonResponse
    {
        $enterprise  = $request->user()->enterprise;
        $application = Application::with('job')
            ->whereHas('job', fn ($q) => $q->where('enterprise_id', $enterprise->id))
            ->findOrFail($id);

        $oldStatus = $application->status;

        $application->update([
            'status' => $request->input('status'),
            'notes'  => $request->input('notes', $application->notes),
        ]);

        // Notify student when accepted or rejected
        $newStatus = $application->status;
        if (in_array($newStatus, ['accepted', 'rejected'], true) && $oldStatus !== $newStatus) {
            SendApplicationStatusChangedJob::dispatch($application, $application->job);
        }

        return response()->json(['success' => true, 'data' => $application->fresh()]);
    }
}
