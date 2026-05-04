<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * GET /api/health
     *
     * Returns JSON status for application, database, and Redis.
     * Used by Docker healthcheck and external uptime monitors.
     */
    public function check(): JsonResponse
    {
        $db    = $this->checkDatabase();
        $redis = $this->checkRedis();

        $allOk = $db === 'ok' && $redis === 'ok';

        return response()->json(
            [
                'status'    => $allOk ? 'ok' : 'degraded',
                'db'        => $db,
                'redis'     => $redis,
                'timestamp' => now()->toISOString(),
            ],
            $allOk ? 200 : 503,
        );
    }

    private function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'ok';
        } catch (\Throwable) {
            return 'fail';
        }
    }

    private function checkRedis(): string
    {
        try {
            $pong = Redis::ping();
            // phpredis returns true; predis returns '+PONG'
            return ($pong === true || $pong === '+PONG') ? 'ok' : 'fail';
        } catch (\Throwable) {
            return 'fail';
        }
    }
}
