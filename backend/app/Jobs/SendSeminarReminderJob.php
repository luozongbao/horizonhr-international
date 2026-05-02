<?php

namespace App\Jobs;

use App\Mail\SeminarReminderMail;
use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSeminarReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        protected Seminar             $seminar,
        protected SeminarRegistration $registration,
    ) {}

    public function handle(): void
    {
        Mail::to($this->registration->email)
            ->send(new SeminarReminderMail($this->seminar, $this->registration));
    }
}
