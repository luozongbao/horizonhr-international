<?php

namespace App\Services;

use App\Models\Resume;
use App\Models\Student;
use App\Models\TalentCard;
use Illuminate\Http\UploadedFile;

class ResumeService
{
    public function __construct(private readonly OssService $oss) {}

    /**
     * Upload a resume file and create the DB record.
     * Files are stored under the 'resumes/{student_id}/' key prefix.
     * Resume files are never image-processed — stored as-is.
     */
    public function uploadResume(Student $student, UploadedFile $file): Resume
    {
        $extension = strtolower($file->getClientOriginalExtension());
        // Normalise jpeg → jpg to match the DB enum
        $fileType = $extension === 'jpeg' ? 'jpg' : $extension;

        $key = $this->oss->upload($file, "resumes/{$student->id}");

        return Resume::create([
            'student_id' => $student->id,
            'file_path'  => $key,
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
        if ($resume->file_path) {
            $this->oss->delete($resume->file_path);
        }

        $resume->delete();
    }

    /**
     * Auto-populate or update the talent card from the student's profile.
     * Called automatically when an admin approves a resume.
     */
    public function generateTalentCard(Student $student): TalentCard
    {
        return TalentCard::updateOrCreate(
            ['student_id' => $student->id],
            ['display_name' => $student->name, 'university' => null]
        );
    }

    /**
     * Return a presigned URL for accessing the resume file (1-hour expiry).
     * Falls back to a plain public URL in local development.
     */
    public function fileUrl(Resume $resume): string
    {
        return $this->oss->presignedUrl($resume->file_path, 3600);
    }
}
