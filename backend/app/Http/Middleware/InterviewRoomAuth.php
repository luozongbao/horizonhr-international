<?php

namespace App\Http\Middleware;

use App\Models\Interview;
use App\Services\InterviewService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class InterviewRoomAuth
{
    public function __construct(private readonly InterviewService $interviewService) {}

    /**
     * Accept either:
     *   1. Standard Sanctum Bearer token (admin / enterprise / logged-in student)
     *   2. X-Room-Token header — signed JWT for student joining via email link
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Path 1: standard Sanctum Bearer
        if ($request->bearerToken()) {
            Auth::shouldUse('sanctum');
            if (Auth::check()) {
                return $next($request);
            }
        }

        // Path 2: X-Room-Token header
        $roomToken = $request->header('X-Room-Token');
        if ($roomToken) {
            try {
                $payload = $this->interviewService->verifyRoomToken($roomToken);
            } catch (\RuntimeException $e) {
                return response()->json([
                    'success' => false,
                    'error'   => ['code' => 'INVALID_ROOM_TOKEN', 'message' => $e->getMessage()],
                ], 401);
            }

            // Bind the decoded payload to the request so the controller can use it
            $request->attributes->set('room_token_payload', $payload);
            $request->attributes->set('auth_via_room_token', true);

            return $next($request);
        }

        return response()->json([
            'success' => false,
            'error'   => ['code' => 'UNAUTHENTICATED', 'message' => 'Authentication required.'],
        ], 401);
    }
}
