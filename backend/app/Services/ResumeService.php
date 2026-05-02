<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\Student;
use App\Models\TalentCard;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ResumeService
{
    /**
     * Upload a resume file and create the DB record.
     *
     * Files are stored on the `public` disk under `resumes/{student_id}/`.
     * TASK-016: Swap Storage::disk('public') for OssService::upload() when OSS is ready.
     */
    public function uploadResume(Student $student, UploadedFile $file): Resume
    {
        $extension = strtolower($file->getClientOriginalExtension());
        // Normalise jpeg → jpg to match the DB enum
        $fileType  = $extension === 'jpeg' ? 'jpg' : $extension;
        $filename  = Str::uuid() . '.' . $extension;
        $directory = "resumes/{$student->id}";

        $file->storeAs($directory, $filename, 'public');

        $filePath = "{$directory}/{$filename}";

        return Resume::create([
            'student_id' => $student->id,
            'file_path'  => $filePath,
            'file_name'  => $file->getClientOriginalName(),
            'file_type'  => $fileType,
            'file_size'  => $file->getSize(),
            'visibility' => 'enterprise_visible',
            'status'     => 'pending',
        ]);
    }

    /**
     * Delete a resume file from storage and remove the DB record.
     */
    public function deleteResume(Resume $resume): void
    {
        // TASK-016: call OssService::delete($resume->file_path) instead
        if (Storage::disk('public')->exists($resume->file_path)) {
            Storage::disk('public')->delete($resume->file_path);
        }

        $resume->delete();
    }

    /**
     * Auto-populate or update the talent card from the student's profile.
     * Called automatically when an admin approves a resume.
     */
    public function generateTalentCard(Student $student): TalentCard
    {
        $data = [
            'display_name' => $student->name,
            'university'   => null,
        ];

        return TalentCard::updateOrCreate(
            ['student_id' => $student->id],
            $data
        );
    }

    /**
     * Return a URL for accessing the resume file.
     *
     * TASK-016: Replace with a presigned OSS URL (1-hour expiry) when OSS is integrated.
     * For now, returns the public storage URL.
     */
    public function fileUrl(Resume $resume): string
    {
        return Storage::disk('public')->url($resume->file_path);
    }
}
