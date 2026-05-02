<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprise\UpdateEnterpriseProfileRequest;
use App\Services\OssService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private readonly OssService $oss) {}
    /**
     * GET /api/enterprise/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user       = $request->user();
        $enterprise = $user->enterprise;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                => $user->id,
                'email'             => $user->email,
                'status'            => $user->status,
                'enterprise_status' => $user->enterprise_status,
                'prefer_lang'       => $user->prefer_lang,
                'profile'           => $enterprise,
            ],
        ]);
    }

    /**
     * PUT /api/enterprise/profile
     */
    public function update(UpdateEnterpriseProfileRequest $request): JsonResponse
    {
        $user       = $request->user();
        $enterprise = $user->enterprise;
        $data       = $request->validated();

        if (array_key_exists('prefer_lang', $data)) {
            $user->update(['prefer_lang' => $data['prefer_lang']]);
            unset($data['prefer_lang']);
        }

        $enterprise->update($data);

        return response()->json([
            'success' => true,
            'data'    => $enterprise->fresh(),
        ]);
    }

    /**
     * POST /api/enterprise/profile/logo
     * Accept images up to 2 MB; resize to max 800×400 preserving aspect ratio, stored as PNG.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $enterprise = $request->user()->enterprise;

        // Delete old logo if we have its storage key
        if ($enterprise->logo_key) {
            $this->oss->delete($enterprise->logo_key);
        }

        $key = $this->oss->uploadLogo(
            $request->file('logo'),
            "logos/enterprises/{$enterprise->id}"
        );

        $url = $this->oss->publicUrl($key);

        $enterprise->update(['logo' => $url, 'logo_key' => $key]);

        return response()->json([
            'success' => true,
            'data'    => ['logo' => $url],
        ]);
    }
}
