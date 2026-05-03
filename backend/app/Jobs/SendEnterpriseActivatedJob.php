<?php

namespace App\Jobs;

use App\Mail\EnterpriseActivatedMail;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEnterpriseActivatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(protected User $user) {}

    public function handle(EmailService $emailService): void
    {
        $name = $this->user->enterprise->company_name ?? '';

        $emailService->send(
            new EnterpriseActivatedMail($this->user),
            $this->user->email,
            $name,
        );
    }
}

