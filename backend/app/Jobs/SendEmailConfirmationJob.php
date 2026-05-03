<?php

namespace App\Jobs;

use App\Mail\EmailConfirmationMail;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailConfirmationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(
        protected User   $user,
        protected string $token,
    ) {}

    public function handle(EmailService $emailService): void
    {
        $name = $this->user->student->name
            ?? $this->user->enterprise->company_name
            ?? '';

        $lang = $this->user->student->prefer_lang ?? 'en';

        $emailService->send(
            new EmailConfirmationMail($this->user, $this->token, $lang),
            $this->user->email,
            $name,
        );
    }
}

