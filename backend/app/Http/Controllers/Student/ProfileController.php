<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\Student\UpdateStudentProfileRequest;
use App\Services\OssService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    public function __construct(private readonly OssService $oss) {}
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
     * Accept image/*, max 2MB, resize to max 500×500 JPEG.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'max:2048'],
        ]);

        $user    = $request->user()->load('student');
        $student = $user->student;

        // Delete old avatar if tracked by key
        if ($student->avatar_key) {
            $this->oss->delete($student->avatar_key);
        }

        $key = $this->oss->uploadAvatar(
            $request->file('avatar'),
            "avatars/students/{$student->id}"
        );

        $url = $this->oss->publicUrl($key);

        $student->update(['avatar' => $url, 'avatar_key' => $key]);

        return response()->json([
            'success' => true,
            'data'    => ['avatar' => $url],
        ]);
    }

    /**
     * PUT /api/student/profile/password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password'      => ['required', 'string'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['The current password is incorrect.'],
            ]);
        }

        $user->update(['password' => Hash::make($data['password'])]);

        return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
    }
}
