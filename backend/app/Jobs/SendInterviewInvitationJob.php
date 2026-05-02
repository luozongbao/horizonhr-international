<?php

namespace App\Jobs;

use App\Mail\InterviewInvitationMail;
use App\Models\Interview;
use App\Models\Student;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendInterviewInvitationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected Interview $interview,
        protected Student   $student,
        protected string    $joinUrl,
    ) {}

    public function handle(): void
    {
        $studentUser = User::find($this->student->user_id);
        if (!$studentUser) {
            return;
        }

        Mail::to($studentUser->email)->send(
            new InterviewInvitationMail($this->interview, $this->student, $this->joinUrl)
        );
    }
}
