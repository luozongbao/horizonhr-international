<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Interview;
use App\Models\Seminar;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * GET /api/student/dashboard
     * Overview stats and upcoming items for the student.
     */
    public function index(Request $request): JsonResponse
    {
        $user    = $request->user();
        $student = $user->student;

        if (!$student) {
            return response()->json(['success' => true, 'data' => []]);
        }

        $applicationStats = Application::where('student_id', $student->id)
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $upcomingInterviews = Interview::where('student_id', $student->id)
            ->whereIn('status', ['scheduled'])
            ->orderBy('scheduled_at', 'asc')
            ->limit(5)
            ->with('enterprise:id,company_name,logo', 'job:id,title')
            ->get();

        $upcomingSeminars = Seminar::where('status', 'scheduled')
            ->orderBy('starts_at', 'asc')
            ->limit(5)
            ->get(['id', 'title_zh_cn', 'title_en', 'title_th', 'starts_at', 'speaker_name', 'thumbnail']);

        return response()->json([
            'success' => true,
            'data'    => [
                'applications' => [
                    'total'    => array_sum($applicationStats->toArray()),
                    'pending'  => (int) ($applicationStats['pending']  ?? 0),
                    'accepted' => (int) ($applicationStats['accepted'] ?? 0),
                    'rejected' => (int) ($applicationStats['rejected'] ?? 0),
                ],
                'upcoming_interviews' => $upcomingInterviews,
                'upcoming_seminars'   => $upcomingSeminars,
            ],
        ]);
    }
}
