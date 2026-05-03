<?php

namespace App\Jobs;

use App\Mail\SeminarReminderMail;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use App\Models\Student;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSeminarReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(
        protected Seminar             $seminar,
        protected SeminarRegistration $registration,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $lang = 'en';
        if ($this->registration->user_id) {
            $student = Student::where('user_id', $this->registration->user_id)->first();
            $lang    = $student?->prefer_lang ?? 'en';
        }

        $emailService->send(
            new SeminarReminderMail($this->seminar, $this->registration, $lang),
            $this->registration->email,
            $this->registration->name ?? '',
        );
    }
}
