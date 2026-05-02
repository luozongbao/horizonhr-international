<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * GET /api/admin/jobs
     * All jobs across all enterprises with all statuses.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Job::with(['enterprise:id,company_name,logo'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('enterprise_id')) {
            $query->where('enterprise_id', (int) $request->query('enterprise_id'));
        }

        if ($request->filled('search')) {
            $kw = $request->query('search');
            $query->where(fn ($q) =>
                $q->where('title', 'like', '%' . $kw . '%')
                  ->orWhere('description', 'like', '%' . $kw . '%')
            );
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
     * DELETE /api/admin/jobs/{id}
     * Hard-delete any job. Existing applications stay in DB (cascade not set).
     */
    public function destroy(int $id): JsonResponse
    {
        $job = Job::findOrFail($id);
        $job->delete();

        return response()->json(['success' => true, 'message' => 'Job deleted.']);
    }
}
