<?php

namespace App\Services;

use App\Jobs\SendApplicationReceivedJob;
use App\Models\Application;
use App\Models\Job;
use App\Models\Student;
use Illuminate\Validation\ValidationException;

class ApplicationService
{
    /**
     * Apply a student to a job.
     *
     * @throws ValidationException  when job is not published or duplicate application
     */
    public function apply(Student $student, array $data): Application
    {
        $job = Job::findOrFail($data['job_id']);

        // Job must be published (and not expired)
        if ($job->status !== 'published') {
            throw ValidationException::withMessages([
                'job_id' => ['This job is not available for applications.'],
            ]);
        }

        if ($job->expires_at !== null && $job->expires_at->isPast()) {
            throw ValidationException::withMessages([
                'job_id' => ['This job posting has expired.'],
            ]);
        }

        // Duplicate guard — DB unique key also protects this, but give a clear message
        $duplicate = Application::where('job_id', $job->id)
            ->where('student_id', $student->id)
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages([
                'job_id' => ['You have already applied to this job.'],
            ]);
        }

        $application = Application::create([
            'job_id'       => $job->id,
            'student_id'   => $student->id,
            'resume_id'    => $data['resume_id'] ?? null,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status'       => 'pending',
        ]);

        // Notify enterprise asynchronously
        $job->load('enterprise');
        SendApplicationReceivedJob::dispatch($application, $job, $student);

        return $application;
    }
}
