<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInterviewRecordRequest;
use App\Jobs\SendInterviewResultJob;
use App\Models\Interview;
use App\Models\InterviewRecord;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    /**
     * GET /api/admin/interviews
     * All interviews with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Interview::with([
                'student:id,name,avatar',
                'enterprise:id,company_name',
                'job:id,title',
            ])
            ->orderBy('scheduled_at', 'asc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('enterprise_id')) {
            $query->where('enterprise_id', (int) $request->query('enterprise_id'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
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
     * PUT /api/admin/interviews/{id}/record
     * Save notes, result, rating for an interview. Dispatches result email when result is set.
     */
    public function updateRecord(UpdateInterviewRecordRequest $request, int $id): JsonResponse
    {
        $interview = Interview::with('student')->findOrFail($id);

        // Find or create admin record for this interview
        $record = InterviewRecord::firstOrNew([
            'interview_id'     => $interview->id,
            'participant_id'   => $request->user()->id,
            'participant_type' => 'admin',
        ]);

        if (!$record->exists) {
            $record->joined_at = now();
        }

        $validated = $request->validated();
        $oldResult = $record->result;

        $record->fill(array_filter($validated, fn ($v) => $v !== null));
        $record->save();

        // Dispatch student notification when result first set or changed
        if (
            isset($validated['result'])
            && $validated['result'] !== null
            && $validated['result'] !== $oldResult
            && $interview->student
        ) {
            SendInterviewResultJob::dispatch($interview, $record, $interview->student);

            // Mark interview completed if a definitive result given
            if (in_array($validated['result'], ['pass', 'fail', 'no_show'], true)) {
                $interview->update(['status' => 'completed']);
            }
        }

        return response()->json(['success' => true, 'data' => $record->fresh()]);
    }
}
