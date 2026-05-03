<?php

namespace App\Jobs;

use App\Mail\InterviewInvitationMail;
use App\Models\Interview;
use App\Models\Student;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendInterviewInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(
        protected Interview $interview,
        protected Student   $student,
        protected string    $joinUrl,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $studentUser = User::find($this->student->user_id);
        if (!$studentUser) {
            return;
        }

        $lang = $this->student->prefer_lang ?? 'en';

        $emailService->send(
            new InterviewInvitationMail($this->interview, $this->student, $this->joinUrl, $lang),
            $studentUser->email,
            $this->student->name,
        );
    }
}

