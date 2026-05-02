<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settingsService) {}

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
        $this->settingsService->bulkUpdate($request->validated()['settings']);

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
     * Upload site logo; update `logo` setting; clear cache.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $url = $this->storeAsset($request->file('logo'), 'logos');

        Setting::set('logo', $url);
        $this->settingsService->clearCache();

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }

    /**
     * Upload site favicon (.ico or .png); update `favicon` setting; clear cache.
     */
    public function uploadFavicon(Request $request): JsonResponse
    {
        $request->validate([
            'favicon' => ['required', 'file', 'max:2048', 'mimes:ico,png'],
        ]);

        $url = $this->storeAsset($request->file('favicon'), 'favicons');

        Setting::set('favicon', $url);
        $this->settingsService->clearCache();

        return response()->json([
            'success' => true,
            'data'    => ['url' => $url],
        ]);
    }

    /**
     * Store an uploaded file under `public` disk and return its public URL.
     * When OSS integration is available (TASK-016), swap Storage::disk('public')
     * with OssService::upload() here.
     */
    private function storeAsset(\Illuminate\Http\UploadedFile $file, string $directory): string
    {
        $filename = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path     = $file->storeAs("assets/{$directory}", $filename, 'public');

        return Storage::disk('public')->url($path);
    }
}
