<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     * All /api/* routes are excluded (API uses Bearer token auth, not cookies).
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
    ];
}
