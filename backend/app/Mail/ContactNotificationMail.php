<?php

namespace App\Mail;

use App\Models\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Contact $contact) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Contact] ' . $this->contact->subject . ' — ' . $this->contact->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-notification',
            with: [
                'contact'   => $this->contact,
                'adminUrl'  => rtrim(config('app.frontend_url'), '/') . '/admin/contacts/' . $this->contact->id,
            ],
        );
    }
}
