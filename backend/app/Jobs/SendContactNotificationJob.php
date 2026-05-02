<?php

namespace App\Jobs;

use App\Mail\ContactNotificationMail;
use App\Models\Contact;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendContactNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected Contact $contact) {}

    public function handle(): void
    {
        $adminEmail = Setting::where('key', 'contact_email')->value('value');

        if (!$adminEmail) {
            return; // No contact email configured — skip silently
        }

        Mail::to($adminEmail)->send(new ContactNotificationMail($this->contact));
    }
}
