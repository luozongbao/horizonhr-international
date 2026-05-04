<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\OssService;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(
        private readonly SettingsService $settingsService,
        private readonly OssService $oss,
    ) {}

    /**
     * Return ALL settings grouped by `group`.
     */
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $this->settingsService->getAllGrouped(),
        ]);
    }

    /**
     * Bulk-update settings.
     * Expects: { "settings": { "key": "value", ... } }
     */
    public function update(UpdateSettingsRequest $request): JsonResponse
    {
        $this->settingsService->bulkUpdate($request->input('settings', []));

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully.',
        ]);
    }

    /**
     * Test SMTP connectivity using the current DB settings.
     * Does NOT send an email — only opens a TCP socket.
     */
    public function testSmtp(): JsonResponse
    {
        $result = $this->settingsService->testSmtp();

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error'   => [
                'code'    => $result['code'],
                'message' => $result['message'],
            ],
        ], 422);
    }

    /**
     * Upload site logo (max 2 MB); resize to max 800×400, stored as PNG.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $key = $this->oss->uploadLogo($request->file('logo'), 'assets/logos');
        $url = $this->oss->publicUrl($key);

        Setting::set('logo', $url);
        Setting::set('logo_key', $key);
        $this->settingsService->clearCache();

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }

    /**
     * Upload site favicon (.ico or .png, max 2 MB); no resizing.
     */
    public function uploadFavicon(Request $request): JsonResponse
    {
        $request->validate([
            'favicon' => ['required', 'file', 'max:2048', 'mimes:ico,png'],
        ]);

        $key = $this->oss->upload($request->file('favicon'), 'assets/favicons');
        $url = $this->oss->publicUrl($key);

        Setting::set('favicon', $url);
        Setting::set('favicon_key', $key);
        $this->settingsService->clearCache();

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }
}
