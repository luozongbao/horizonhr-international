<?php

namespace App\Jobs;

use App\Mail\EnterpriseActivatedMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEnterpriseActivatedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected User $user) {}

    public function handle(): void
    {
        Mail::to($this->user->email)->send(new EnterpriseActivatedMail($this->user));
    }
}
