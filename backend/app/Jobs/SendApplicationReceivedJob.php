<?php

namespace App\Jobs;

use App\Mail\JobApplicationReceivedMail;
use App\Models\Application;
use App\Models\Job;
use App\Models\Student;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendApplicationReceivedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(
        protected Application $application,
        protected Job         $job,
        protected Student     $student,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $enterpriseUser = User::where('id', $this->job->enterprise->user_id)->first();
        if (!$enterpriseUser) {
            return;
        }

        $name = $this->job->enterprise->contact_name
            ?? $this->job->enterprise->company_name
            ?? '';

        $emailService->send(
            new JobApplicationReceivedMail($this->application, $this->job, $this->student),
            $enterpriseUser->email,
            $name,
        );
    }
}

