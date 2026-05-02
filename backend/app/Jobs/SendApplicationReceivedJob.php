<?php

namespace App\Jobs;

use App\Mail\JobApplicationReceivedMail;
use App\Models\Application;
use App\Models\Job;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendApplicationReceivedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected Application $application,
        protected Job         $job,
        protected Student     $student,
    ) {}

    public function handle(): void
    {
        // Notify the enterprise contact email (the enterprise user's email)
        $enterpriseUser = User::where('id', $this->job->enterprise->user_id)->first();
        if (!$enterpriseUser) {
            return;
        }

        Mail::to($enterpriseUser->email)->send(
            new JobApplicationReceivedMail($this->application, $this->job, $this->student)
        );
    }
}
