<?php

namespace App\Jobs;

use App\Mail\EnterprisePendingNotifyAdminMail;
use App\Models\User;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEnterprisePendingNotifyAdminJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $queue   = 'emails';
    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(protected User $user) {}

    public function handle(EmailService $emailService): void
    {
        $adminEmails = User::where('role', 'admin')
            ->where('status', 'active')
            ->pluck('email')
            ->toArray();

        if (empty($adminEmails)) {
            return;
        }

        foreach ($adminEmails as $adminEmail) {
            $emailService->send(
                new EnterprisePendingNotifyAdminMail($this->user),
                $adminEmail,
            );
        }
    }
}
