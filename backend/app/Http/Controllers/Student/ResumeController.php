<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreResumeRequest;
use App\Http\Requests\Student\UpdateResumeRequest;
use App\Models\Resume;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResumeController extends Controller
{
    public function __construct(private readonly ResumeService $resumeService) {}

    /**
     * GET /api/student/resumes
     * Return own resumes only.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->student;
        $resumes = Resume::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn (Resume $r) => $this->formatResume($r));

        return response()->json([
            'success' => true,
            'data'    => $resumes,
        ]);
    }

    /**
     * POST /api/student/resumes
     * Upload a new resume file.
     */
    public function store(StoreResumeRequest $request): JsonResponse
    {
        $student = $request->user()->student;
        $resume  = $this->resumeService->uploadResume($student, $request->file('file'));

        if ($request->filled('visibility')) {
            $resume->update(['visibility' => $request->visibility]);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatResume($resume),
        ], 201);
    }

    /**
     * PUT /api/student/resumes/{id}
     * Update visibility only — students cannot change review status.
     */
    public function update(UpdateResumeRequest $request, int $id): JsonResponse
    {
        $student = $request->user()->student;
        $resume  = Resume::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $resume->update(['visibility' => $request->visibility]);

        return response()->json([
            'success' => true,
            'data'    => $this->formatResume($resume->fresh()),
        ]);
    }

    /**
     * DELETE /api/student/resumes/{id}
     * Delete file from storage and DB row.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $student = $request->user()->student;
        $resume  = Resume::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        $this->resumeService->deleteResume($resume);

        return response()->json([
            'success' => true,
            'message' => 'Resume deleted.',
        ]);
    }

    private function formatResume(Resume $r): array
    {
        return [
            'id'          => $r->id,
            'file_name'   => $r->file_name,
            'file_type'   => $r->file_type,
            'file_size'   => $r->file_size,
            'visibility'  => $r->visibility,
            'status'      => $r->status,
            'reviewed_at' => $r->reviewed_at,
            'created_at'  => $r->created_at,
            'url'         => $this->resumeService->fileUrl($r),
        ];
    }
}
