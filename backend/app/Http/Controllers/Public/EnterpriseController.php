<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Enterprise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EnterpriseController extends Controller
{
    /**
     * GET /api/public/enterprises
     * List verified enterprises. Filters: industry, search (company_name).
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = Enterprise::where('verified', true)
            ->withCount(['jobs' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('company_name');

        if ($request->filled('industry')) {
            $query->where('industry', $request->query('industry'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where('company_name', 'like', '%' . $search . '%');
        }

        $paginated = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $paginated->getCollection()->map(fn (Enterprise $e) => $this->formatEnterprise($e)),
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'last_page'    => $paginated->lastPage(),
            ],
        ]);
    }

    /**
     * GET /api/public/enterprises/{id}
     */
    public function show(int $id): JsonResponse
    {
        $enterprise = Enterprise::where('verified', true)
            ->withCount(['jobs' => fn ($q) => $q->where('status', 'published')])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $this->formatEnterprise($enterprise),
        ]);
    }

    private function formatEnterprise(Enterprise $e): array
    {
        return [
            'id'           => $e->id,
            'company_name' => $e->company_name,
            'industry'     => $e->industry,
            'logo'         => $e->logo,
            'scale'        => $e->scale,
            'description'  => $e->description,
            'website'      => $e->website,
            'address'      => $e->address,
            'jobs_count'   => $e->jobs_count,
        ];
    }
}
