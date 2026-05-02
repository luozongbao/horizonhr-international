<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreApplicationRequest;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ApplicationController extends Controller
{
    public function __construct(private readonly ApplicationService $applicationService) {}

    /**
     * GET /api/student/applications
     * List own applications with job + enterprise + resume info.
     */
    public function index(Request $request): JsonResponse
    {
        $student = $request->user()->student;

        $applications = Application::with([
                'job:id,title,location,job_type,salary_min,salary_max,salary_currency,enterprise_id',
                'job.enterprise:id,company_name,logo',
                'resume:id,file_name,file_type',
            ])
            ->where('student_id', $student->id)
            ->orderBy('applied_at', 'desc')
            ->paginate(20);

        $items = $applications->getCollection()->map(fn (Application $app) => [
            'id'           => $app->id,
            'status'       => $app->status,
            'applied_at'   => $app->applied_at,
            'cover_letter' => $app->cover_letter,
            'job'          => $app->job ? [
                'id'           => $app->job->id,
                'title'        => $app->job->title,
                'location'     => $app->job->location,
                'job_type'     => $app->job->job_type,
                'salary_range' => $this->salaryRange($app->job),
                'enterprise'   => $app->job->enterprise ? [
                    'id'           => $app->job->enterprise->id,
                    'company_name' => $app->job->enterprise->company_name,
                    'logo'         => $app->job->enterprise->logo,
                ] : null,
            ] : null,
            'resume' => $app->resume ? [
                'id'        => $app->resume->id,
                'file_name' => $app->resume->file_name,
                'file_type' => $app->resume->file_type,
            ] : null,
        ]);

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $applications->currentPage(),
                'per_page'     => $applications->perPage(),
                'total'        => $applications->total(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/student/applications
     */
    public function store(StoreApplicationRequest $request): JsonResponse
    {
        $student = $request->user()->student;

        try {
            $application = $this->applicationService->apply($student, $request->validated());
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'VALIDATION_ERROR',
                    'message' => 'The given data was invalid.',
                    'errors'  => $e->errors(),
                ],
            ], 422);
        }

        return response()->json(['success' => true, 'data' => $application], 201);
    }

    /**
     * DELETE /api/student/applications/{id}
     * Withdraw — only allowed when status is pending.
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $student     = $request->user()->student;
        $application = Application::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        if ($application->status !== 'pending') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'CANNOT_WITHDRAW',
                    'message' => 'You can only withdraw a pending application.',
                ],
            ], 422);
        }

        $application->update(['status' => 'withdrawn']);

        return response()->json(['success' => true, 'message' => 'Application withdrawn.']);
    }

    private function salaryRange($job): ?string
    {
        if (!$job->salary_min && !$job->salary_max) {
            return null;
        }
        $currency = $job->salary_currency ?? 'CNY';
        if ($job->salary_min && $job->salary_max) {
            return "{$currency} {$job->salary_min} – {$job->salary_max}";
        }
        return $job->salary_min
            ? "{$currency} {$job->salary_min}+"
            : "Up to {$currency} {$job->salary_max}";
    }
}
