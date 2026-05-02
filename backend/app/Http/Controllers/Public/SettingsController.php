<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService) {}

    /**
     * Return a safe public subset of site settings.
     * SMTP credentials and system flags are never included.
     */
    public function publicConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->settingsService->getPublicSettings(),
        ]);
    }
}
