<?php

namespace App\Jobs;

use App\Mail\ApplicationStatusChangedMail;
use App\Models\Application;
use App\Models\Job;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendApplicationStatusChangedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(
        protected Application $application,
        protected Job         $job,
    ) {
        $this->onQueue('emails'); }

    public function handle(EmailService $emailService): void
    {
        $studentUser = User::where('id', $this->application->student->user_id)->first();
        if (!$studentUser) {
            return;
        }

        $lang = $this->application->student->prefer_lang ?? 'en';

        $emailService->send(
            new ApplicationStatusChangedMail($this->application, $this->job, $lang),
            $studentUser->email,
            $this->application->student->name ?? '',
        );
    }
}

