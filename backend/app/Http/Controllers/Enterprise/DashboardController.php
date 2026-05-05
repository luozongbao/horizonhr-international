<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Job;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/enterprise/dashboard
     * Overview stats and recent activity for the enterprise.
     */
    public function index(Request $request): JsonResponse
    {
        $user       = $request->user();
        $enterprise = $user->enterprise;

        if (!$enterprise) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $jobStats = Job::where('enterprise_id', $enterprise->id)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $jobIds = Job::where('enterprise_id', $enterprise->id)->pluck('id');

        $applicationStats = Application::whereIn('job_id', $jobIds)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $upcomingInterviews = Interview::where('enterprise_id', $enterprise->id)
            ->whereIn('status', ['scheduled'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->with('student:id,name,avatar', 'job:id,title')
            ->get();

        $recentApplications = Application::whereIn('job_id', $jobIds)
            ->orderBy('applied_at', 'desc')
            ->limit(5)
            ->with('student:id,name,avatar', 'job:id,title')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'jobs' => [
                    'total'     => array_sum($jobStats->toArray()),
                    'published' => (int) ($jobStats['published'] ?? 0),
                    'closed'    => (int) ($jobStats['closed']    ?? 0),
                    'draft'     => (int) ($jobStats['draft']     ?? 0),
                ],
                'applications' => [
                    'total'    => array_sum($applicationStats->toArray()),
                    'pending'  => (int) ($applicationStats['pending']  ?? 0),
                    'accepted' => (int) ($applicationStats['accepted'] ?? 0),
                    'rejected' => (int) ($applicationStats['rejected'] ?? 0),
                ],
                'upcoming_interviews' => $upcomingInterviews,
                'recent_applications' => $recentApplications,
            ],
        ]);
    }
}
