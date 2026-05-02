<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprise\StoreJobRequest;
use App\Http\Requests\Enterprise\UpdateJobRequest;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * GET /api/enterprise/jobs
     * All jobs belonging to this enterprise (all statuses).
     */
    public function index(Request $request): JsonResponse
    {
        $enterprise = $request->user()->enterprise;

        $jobs = Job::where('enterprise_id', $enterprise->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $jobs->items(),
            'meta'    => [
                'current_page' => $jobs->currentPage(),
                'per_page'     => $jobs->perPage(),
                'total'        => $jobs->total(),
                'last_page'    => $jobs->lastPage(),
            ],
        ]);
    }

    /**
     * POST /api/enterprise/jobs  [enterprise.verified middleware on route]
     */
    public function store(StoreJobRequest $request): JsonResponse
    {
        $enterprise = $request->user()->enterprise;

        $job = Job::create(array_merge(
            $request->validated(),
            ['enterprise_id' => $enterprise->id, 'status' => 'draft']
        ));

        return response()->json(['success' => true, 'data' => $job], 201);
    }

    /**
     * PUT /api/enterprise/jobs/{id}
     */
    public function update(UpdateJobRequest $request, int $id): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $job        = Job::where('id', $id)
            ->where('enterprise_id', $enterprise->id)
            ->firstOrFail();

        $job->update($request->validated());

        return response()->json(['success' => true, 'data' => $job->fresh()]);
    }

    /**
     * DELETE /api/enterprise/jobs/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $job        = Job::where('id', $id)
            ->where('enterprise_id', $enterprise->id)
            ->firstOrFail();

        $job->delete();

        return response()->json(['success' => true, 'message' => 'Job deleted.']);
    }

    /**
     * PUT /api/enterprise/jobs/{id}/publish  [enterprise.verified middleware on route]
     */
    public function publish(Request $request, int $id): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $job        = Job::where('id', $id)
            ->where('enterprise_id', $enterprise->id)
            ->firstOrFail();

        if (!in_array($job->status, ['draft', 'closed'], true)) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'Only draft or closed jobs can be published.'],
            ], 422);
        }

        $job->update(['status' => 'published', 'published_at' => now()]);

        return response()->json(['success' => true, 'data' => $job->fresh()]);
    }

    /**
     * PUT /api/enterprise/jobs/{id}/close
     */
    public function close(Request $request, int $id): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $job        = Job::where('id', $id)
            ->where('enterprise_id', $enterprise->id)
            ->firstOrFail();

        if ($job->status !== 'published') {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'INVALID_STATUS', 'message' => 'Only published jobs can be closed.'],
            ], 422);
        }

        $job->update(['status' => 'closed']);

        return response()->json(['success' => true, 'data' => $job->fresh()]);
    }

    /**
     * GET /api/enterprise/jobs/{id}/applications
     */
    public function applications(Request $request, int $id): JsonResponse
    {
        $enterprise = $request->user()->enterprise;
        $job        = Job::where('id', $id)
            ->where('enterprise_id', $enterprise->id)
            ->firstOrFail();

        $applications = $job->applications()
            ->with(['student:id,name,nationality,avatar', 'resume:id,file_name,file_type'])
            ->orderBy('applied_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $applications->items(),
            'meta'    => [
                'current_page' => $applications->currentPage(),
                'per_page'     => $applications->perPage(),
                'total'        => $applications->total(),
                'last_page'    => $applications->lastPage(),
            ],
        ]);
    }
}
