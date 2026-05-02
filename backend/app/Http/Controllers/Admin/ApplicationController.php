<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    /**
     * GET /api/admin/applications
     * All applications regardless of enterprise; filter by job_id, student_id, status.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Application::with([
                'job:id,title,enterprise_id',
                'job.enterprise:id,company_name',
                'student:id,name,nationality',
                'resume:id,file_name,file_type',
            ])
            ->orderBy('applied_at', 'desc');

        if ($request->filled('job_id')) {
            $query->where('job_id', (int) $request->query('job_id'));
        }

        if ($request->filled('student_id')) {
            $query->where('student_id', (int) $request->query('student_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('enterprise_id')) {
            $query->whereHas('job', fn ($q) => $q->where('enterprise_id', (int) $request->query('enterprise_id')));
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
}
