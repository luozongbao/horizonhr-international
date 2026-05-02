<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReviewResumeRequest;
use App\Models\Resume;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function __construct(private readonly ResumeService $resumeService) {}

    /**
     * GET /api/admin/resumes
     * Paginated list with filters. Includes student profile summary.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Resume::with(['student'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('visibility')) {
            $query->where('visibility', $request->query('visibility'));
        }

        if ($request->filled('nationality')) {
            $query->whereHas('student', fn ($q) => $q->where('nationality', $request->query('nationality')));
        }

        if ($request->filled('search')) {
            $search = '%' . $request->query('search') . '%';
            $query->whereHas('student', fn ($q) => $q->where('name', 'like', $search));
        }

        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(fn (Resume $r) => [
            'id'          => $r->id,
            'file_name'   => $r->file_name,
            'file_type'   => $r->file_type,
            'file_size'   => $r->file_size,
            'visibility'  => $r->visibility,
            'status'      => $r->status,
            'reviewed_at' => $r->reviewed_at,
            'created_at'  => $r->created_at,
            'url'         => $this->resumeService->fileUrl($r),
            'student'     => $r->student ? [
                'id'          => $r->student->id,
                'name'        => $r->student->name,
                'nationality' => $r->student->nationality,
                'university'  => $r->student->talentCard?->university,
            ] : null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * PUT /api/admin/resumes/{id}/review
     * Approve or reject. On approve, auto-generate/update talent card.
     */
    public function review(ReviewResumeRequest $request, int $id): JsonResponse
    {
        $resume = Resume::with('student')->findOrFail($id);

        $resume->update([
            'status'      => $request->status,
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        if ($request->status === 'approved' && $resume->student) {
            $this->resumeService->generateTalentCard($resume->student);
        }

        return response()->json([
            'success' => true,
            'data'    => $resume->fresh(),
            'message' => 'Resume ' . $request->status . '.',
        ]);
    }
}
