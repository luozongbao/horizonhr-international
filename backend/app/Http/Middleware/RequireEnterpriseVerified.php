<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireEnterpriseVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->enterprise_status !== 'enterprise_verified') {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'ENTERPRISE_NOT_VERIFIED',
                    'message' => 'Your enterprise account has not been verified yet.',
                ],
            ], 403);
        }

        return $next($request);
    }
}
