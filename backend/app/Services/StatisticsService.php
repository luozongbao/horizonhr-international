<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsService
{
    /**
     * Cache TTL: 5 minutes.
     */
    private const CACHE_TTL = 300;

    /**
     * Resolve a Carbon start date from a period string.
     * Returns null for 'all' (no date constraint).
     */
    private function resolveStartDate(string $period): ?Carbon
    {
        return match ($period) {
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            '90d' => now()->subDays(90),
            '1y'  => now()->subYear(),
            'all' => null,
            default => now()->subDays(30),
        };
    }

    /**
     * Number of days in the period for daily trend generation.
     */
    private function periodDays(string $period): int
    {
        return match ($period) {
            '7d'  => 7,
            '30d' => 30,
            '90d' => 90,
            '1y'  => 365,
            'all' => 30, // default to last 30 days for 'all'
            default => 30,
        };
    }

    /**
     * Return aggregated platform statistics, cached per period.
     */
    public function getStats(string $period = '30d'): array
    {
        $cacheKey = 'admin_stats:' . $period;

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($period) {
            $since = $this->resolveStartDate($period);

            return [
                'period'      => $period,
                'users'       => $this->usersStats($since),
                'resumes'     => $this->resumesStats($since),
                'interviews'  => $this->interviewsStats($since),
                'seminars'    => $this->seminarsStats(),
                'jobs'        => $this->jobsStats($since),
                'applications'=> $this->applicationsStats(),
                'daily_trend' => $this->dailyTrend($period),
            ];
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // Individual stat aggregators
    // ──────────────────────────────────────────────────────────────────

    private function usersStats(?Carbon $since): array
    {
        $byRole = DB::table('users')
            ->selectRaw("role, COUNT(*) as cnt")
            ->groupBy('role')
            ->pluck('cnt', 'role');

        $newInPeriod = $since
            ? DB::table('users')->where('created_at', '>=', $since)->count()
            : DB::table('users')->count();

        return [
            'total'         => array_sum($byRole->toArray()),
            'students'      => (int) ($byRole['student']     ?? 0),
            'enterprises'   => (int) ($byRole['enterprise']  ?? 0),
            'admins'        => (int) ($byRole['admin']        ?? 0),
            'new_in_period' => $newInPeriod,
        ];
    }

    private function resumesStats(?Carbon $since): array
    {
        $byStatus = DB::table('resumes')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $newInPeriod = $since
            ? DB::table('resumes')->where('created_at', '>=', $since)->count()
            : DB::table('resumes')->count();

        return [
            'total'         => array_sum($byStatus->toArray()),
            'pending'       => (int) ($byStatus['pending']  ?? 0),
            'approved'      => (int) ($byStatus['approved'] ?? 0),
            'rejected'      => (int) ($byStatus['rejected'] ?? 0),
            'new_in_period' => $newInPeriod,
        ];
    }

    private function interviewsStats(?Carbon $since): array
    {
        $byStatus = DB::table('interviews')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $newInPeriod = $since
            ? DB::table('interviews')->where('created_at', '>=', $since)->count()
            : DB::table('interviews')->count();

        return [
            'total'         => array_sum($byStatus->toArray()),
            'scheduled'     => (int) ($byStatus['scheduled']   ?? 0),
            'completed'     => (int) ($byStatus['completed']   ?? 0),
            'cancelled'     => (int) ($byStatus['cancelled']   ?? 0),
            'new_in_period' => $newInPeriod,
        ];
    }

    private function seminarsStats(): array
    {
        $byStatus = DB::table('seminars')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'total'     => array_sum($byStatus->toArray()),
            'scheduled' => (int) ($byStatus['scheduled'] ?? 0),
            'live'      => (int) ($byStatus['live']      ?? 0),
            'ended'     => (int) ($byStatus['ended']     ?? 0),
        ];
    }

    private function jobsStats(?Carbon $since): array
    {
        $byStatus = DB::table('jobs')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        $newInPeriod = $since
            ? DB::table('jobs')->where('created_at', '>=', $since)->count()
            : DB::table('jobs')->count();

        return [
            'total'         => array_sum($byStatus->toArray()),
            'published'     => (int) ($byStatus['published'] ?? 0),
            'closed'        => (int) ($byStatus['closed']    ?? 0),
            'new_in_period' => $newInPeriod,
        ];
    }

    private function applicationsStats(): array
    {
        $byStatus = DB::table('applications')
            ->selectRaw("status, COUNT(*) as cnt")
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return [
            'total'    => array_sum($byStatus->toArray()),
            'pending'  => (int) ($byStatus['pending']  ?? 0),
            'accepted' => (int) ($byStatus['accepted'] ?? 0),
        ];
    }

    // ──────────────────────────────────────────────────────────────────
    // Daily trend
    // ──────────────────────────────────────────────────────────────────

    /**
     * Generate a date series (zero-filled) for the period with daily counts.
     * Produces a clean array suitable for charting even on days with no activity.
     */
    private function dailyTrend(string $period): array
    {
        $days  = $this->periodDays($period);
        $since = now()->subDays($days)->startOfDay();

        // Fetch daily counts in one query each — raw GROUP BY DATE
        $userRows = DB::table('users')
            ->selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->pluck('cnt', 'd');

        $resumeRows = DB::table('resumes')
            ->selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->pluck('cnt', 'd');

        $interviewRows = DB::table('interviews')
            ->selectRaw("DATE(created_at) as d, COUNT(*) as cnt")
            ->where('created_at', '>=', $since)
            ->groupByRaw('DATE(created_at)')
            ->pluck('cnt', 'd');

        // Build zero-filled date series
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $trend[] = [
                'date'            => $date,
                'new_users'       => (int) ($userRows[$date]      ?? 0),
                'new_resumes'     => (int) ($resumeRows[$date]    ?? 0),
                'new_interviews'  => (int) ($interviewRows[$date] ?? 0),
            ];
        }

        return $trend;
    }
}
