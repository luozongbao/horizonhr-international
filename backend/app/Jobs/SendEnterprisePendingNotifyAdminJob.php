<?php

namespace App\Jobs;

use App\Mail\EnterprisePendingNotifyAdminMail;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEnterprisePendingNotifyAdminJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected User $user) {}

    public function handle(): void
    {
        // Notify all active admin accounts
        $adminEmails = User::where('role', 'admin')
            ->where('status', 'active')
            ->pluck('email')
            ->toArray();

        if (empty($adminEmails)) {
            return;
        }

        Mail::to($adminEmails)->send(new EnterprisePendingNotifyAdminMail($this->user));
    }
}
