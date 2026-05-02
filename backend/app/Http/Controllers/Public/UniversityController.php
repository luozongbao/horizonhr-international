<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\University;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UniversityController extends Controller
{
    /**
     * GET /api/public/universities
     * Filterable by location_city, location_region, program_types, search.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->query('per_page', 20)), 100);

        $query = University::query()->orderBy('ranking', 'asc')->orderBy('name_en', 'asc');

        if ($request->filled('location_city')) {
            $query->where('location_city', $request->query('location_city'));
        }

        if ($request->filled('location_region')) {
            $query->where('location_region', $request->query('location_region'));
        }

        if ($request->filled('program_types')) {
            // JSON array contains check — MySQL JSON_CONTAINS
            $type = $request->query('program_types');
            $query->whereRaw('JSON_CONTAINS(program_types, ?)', [json_encode($type)]);
        }

        if ($request->filled('search')) {
            $kw = '%' . $request->query('search') . '%';
            $query->where(function ($q) use ($kw) {
                $q->where('name_en', 'like', $kw)
                  ->orWhere('name_zh_cn', 'like', $kw)
                  ->orWhere('name_th', 'like', $kw);
            });
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
     * GET /api/public/universities/{id}
     */
    public function show(int $id): JsonResponse
    {
        $university = University::findOrFail($id);

        return response()->json(['success' => true, 'data' => $university]);
    }
}
