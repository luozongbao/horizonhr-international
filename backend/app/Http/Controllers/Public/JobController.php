<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JobController extends Controller
{
    /**
     * GET /api/public/jobs
     * Published, non-expired jobs with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Job::with(['enterprise:id,company_name,logo,industry,verified'])
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->whereHas('enterprise', fn ($q) => $q->where('verified', true))
            ->orderBy('published_at', 'desc');

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->query('location') . '%');
        }

        if ($request->filled('job_type')) {
            $query->where('job_type', $request->query('job_type'));
        }

        if ($request->filled('salary_min')) {
            $query->where('salary_max', '>=', (int) $request->query('salary_min'));
        }

        if ($request->filled('salary_max')) {
            $query->where('salary_min', '<=', (int) $request->query('salary_max'));
        }

        if ($request->filled('search')) {
            $kw = $request->query('search');
            $query->where(fn ($q) =>
                $q->where('title', 'like', '%' . $kw . '%')
                  ->orWhere('description', 'like', '%' . $kw . '%')
            );
        }

        if ($request->filled('enterprise_id')) {
            $query->where('enterprise_id', (int) $request->query('enterprise_id'));
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
     * GET /api/public/jobs/{id}
     * Job detail with enterprise info. Increments view_count atomically.
     */
    public function show(int $id): JsonResponse
    {
        $job = Job::with(['enterprise:id,company_name,logo,industry,website,verified'])
            ->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
            ->whereHas('enterprise', fn ($q) => $q->where('verified', true))
            ->findOrFail($id);

        // Atomic view count increment — no race condition
        Job::where('id', $id)->increment('view_count');
        $job->view_count += 1;

        return response()->json([
            'success' => true,
            'data'    => $job,
        ]);
    }
}
