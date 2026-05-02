<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return response()->json([
                'success' => false,
                'error'   => ['code' => 'UNAUTHENTICATED', 'message' => 'Unauthenticated.'],
            ], 401);
        }

        if ($user->status === 'suspended') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_SUSPENDED',
                    'message' => 'Your account has been suspended. Please contact support.',
                ],
            ], 403);
        }

        if ($user->status === 'deleted') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ACCOUNT_NOT_FOUND',
                    'message' => 'Account not found.',
                ],
            ], 403);
        }

        return $next($request);
    }
}
