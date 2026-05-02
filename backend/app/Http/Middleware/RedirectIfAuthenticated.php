<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     * This middleware is used by the `guest` alias — not applied to any API routes.
     * Kept for Kernel compatibility.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (Auth::guard($guard)->check()) {
                return response()->json([
                    'success' => false,
                    'error'   => ['code' => 'ALREADY_AUTHENTICATED', 'message' => 'Already authenticated.'],
                ], 403);
            }
        }

        return $next($request);
    }
}
