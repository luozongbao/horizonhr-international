<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\StatisticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
    public function __construct(private readonly StatisticsService $statistics) {}

    /**
     * GET /api/admin/stats
     * Query params: ?period=7d|30d|90d|1y|all (default: 30d)
     */
    public function index(Request $request): JsonResponse
    {
        $period = $request->query('period', '30d');

        // Validate allowed values; fall back to 30d
        if (!in_array($period, ['7d', '30d', '90d', '1y', 'all'], true)) {
            $period = '30d';
        }

        $data = $this->statistics->getStats($period);

        return response()->json(['success' => true, 'data' => $data]);
    }
}
