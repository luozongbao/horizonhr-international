<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Models\Resume;
use App\Models\TalentCard;
use App\Services\ResumeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalentController extends Controller
{
    public function __construct(private readonly ResumeService $resumeService) {}

    /**
     * GET /api/enterprise/talent
     * Talent cards where the student has at least one enterprise_visible or public
     * approved resume. Includes the resumes visible to this enterprise.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = TalentCard::with(['student.resume' => function ($q) {
                $q->where('status', 'approved')
                  ->whereIn('visibility', ['enterprise_visible', 'public']);
            }])
            ->whereHas('student.resume', fn ($q) =>
                $q->where('status', 'approved')
                  ->whereIn('visibility', ['enterprise_visible', 'public'])
            )
            ->whereIn('status', ['visible', 'featured'])
            ->orderByRaw("CASE status WHEN 'featured' THEN 0 ELSE 1 END")
            ->orderBy('updated_at', 'desc');

        if ($request->filled('nationality')) {
            $query->whereHas('student', fn ($q) => $q->where('nationality', $request->query('nationality')));
        }

        if ($request->filled('major')) {
            $query->where('major', 'like', '%' . $request->query('major') . '%');
        }

        if ($request->filled('education')) {
            $query->where('education', $request->query('education'));
        }

        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(fn (TalentCard $tc) => $this->formatCard($tc));

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
     * GET /api/enterprise/talent/{id}
     * Detail view of a talent card — only returns resume download links for
     * enterprise_visible and public resumes.
     */
    public function show(int $id, Request $request): JsonResponse
    {
        $card = TalentCard::with(['student.resume' => function ($q) {
                $q->where('status', 'approved')
                  ->whereIn('visibility', ['enterprise_visible', 'public']);
            }])
            ->whereHas('student.resume', fn ($q) =>
                $q->where('status', 'approved')
                  ->whereIn('visibility', ['enterprise_visible', 'public'])
            )
            ->whereIn('status', ['visible', 'featured'])
            ->findOrFail($id);

        $resumeList = $card->student->resume->map(fn (Resume $r) => [
            'id'        => $r->id,
            'file_name' => $r->file_name,
            'file_type' => $r->file_type,
            'url'       => $this->resumeService->fileUrl($r),
        ]);

        return response()->json([
            'success' => true,
            'data'    => array_merge($this->formatCard($card), ['resumes' => $resumeList]),
        ]);
    }

    private function formatCard(TalentCard $tc): array
    {
        return [
            'id'            => $tc->id,
            'display_name'  => $tc->display_name,
            'major'         => $tc->major,
            'education'     => $tc->education,
            'university'    => $tc->university,
            'languages'     => $tc->languages,
            'skills'        => $tc->skills,
            'work_experience' => $tc->work_experience,
            'job_intention' => $tc->job_intention,
            'status'        => $tc->status,
            'nationality'   => $tc->student?->nationality,
            'avatar'        => $tc->student?->avatar,
            'updated_at'    => $tc->updated_at,
        ];
    }
}
