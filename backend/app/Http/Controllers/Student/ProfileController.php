<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateStudentProfileRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ProfileController extends Controller
{
    /**
     * GET /api/student/profile
     * Return own student profile merged with user data.
     */
    public function show(Request $request): JsonResponse
    {
        $user    = $request->user()->load('student');
        $student = $user->student;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $user->id,
                'email'       => $user->email,
                'status'      => $user->status,
                'prefer_lang' => $user->prefer_lang,
                'profile'     => $student,
            ],
        ]);
    }

    /**
     * PUT /api/student/profile
     */
    public function update(UpdateStudentProfileRequest $request): JsonResponse
    {
        $user    = $request->user()->load('student');
        $student = $user->student;
        $data    = $request->validated();

        // prefer_lang lives on users table
        if (isset($data['prefer_lang'])) {
            $user->update(['prefer_lang' => $data['prefer_lang']]);
            unset($data['prefer_lang']);
        }

        $student->update($data);

        return response()->json([
            'success' => true,
            'data'    => $student->fresh(),
        ]);
    }

    /**
     * POST /api/student/profile/avatar
     * Accept image/*, max 2MB, resize to max 500×500.
     * TASK-016: swap Storage::disk('public') for OssService::upload().
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user    = $request->user()->load('student');
        $student = $user->student;

        $img      = Image::make($request->file('avatar'))->fit(500, 500);
        $filename = Str::uuid() . '.jpg';
        $path     = "avatars/{$student->id}/{$filename}";

        Storage::disk('public')->put($path, $img->encode('jpg', 85));

        // Delete old avatar
        if ($student->avatar) {
            $oldPath = ltrim(parse_url($student->avatar, PHP_URL_PATH), '/storage/');
            Storage::disk('public')->delete($oldPath);
        }

        $url = Storage::disk('public')->url($path);
        $student->update(['avatar' => $url]);

        return response()->json([
            'success' => true,
            'data'    => ['avatar' => $url],
        ]);
    }
}
