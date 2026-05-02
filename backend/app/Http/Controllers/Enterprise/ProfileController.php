<?php

namespace App\Http\Controllers\Enterprise;

use App\Http\Controllers\Controller;
use App\Http\Requests\Enterprise\UpdateEnterpriseProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
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
     * Accept images up to 2 MB; resize to max 400×400 preserving aspect ratio.
     * TASK-016: replace Storage::disk('public') with OssService::upload()
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'max:2048'],
        ]);

        $enterprise = $request->user()->enterprise;

        $image = Image::make($request->file('logo'));
        $image->resize(400, 400, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $filename = Str::uuid() . '.jpg';
        $path     = "logos/{$enterprise->id}/{$filename}";

        // Delete old logo if stored on public disk
        if ($enterprise->logo) {
            $oldPath = ltrim(parse_url($enterprise->logo, PHP_URL_PATH), '/storage/');
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
        }

        // TASK-016: swap Storage::disk('public') for OssService::upload()
        Storage::disk('public')->put($path, $image->encode('jpg', 85)->getEncoded());
        $url = Storage::disk('public')->url($path);

        $enterprise->update(['logo' => $url]);

        return response()->json([
            'success' => true,
            'data'    => ['logo' => $url],
        ]);
    }
}
