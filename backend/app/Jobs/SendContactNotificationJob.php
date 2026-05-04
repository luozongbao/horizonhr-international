<?php

namespace App\Jobs;

use App\Mail\ContactNotificationMail;
use App\Models\Contact;
use App\Models\Setting;
use App\Services\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendContactNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public int    $tries   = 3;
    public array  $backoff = [30, 60, 120];

    public function __construct(protected Contact $contact) {
        $this->onQueue('emails'); }

    public function handle(EmailService $emailService): void
    {
        $adminEmail = Setting::where('key', 'contact_email')->value('value');
        if (!$adminEmail) {
            return;
        }

        $emailService->send(
            new ContactNotificationMail($this->contact),
            $adminEmail,
        );
    }
}
