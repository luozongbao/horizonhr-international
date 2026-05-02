<?php

namespace App\Jobs;

use App\Mail\ApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendApplicationStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected Application $application,
        protected Job         $job,
    ) {}

    public function handle(): void
    {
        $studentUser = User::where('id', $this->application->student->user_id)->first();
        if (!$studentUser) {
            return;
        }

        Mail::to($studentUser->email)->send(
            new ApplicationStatusChangedMail($this->application, $this->job)
        );
    }
}
