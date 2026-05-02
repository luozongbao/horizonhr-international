<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmailConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Verify Your Email — HorizonHR');
    }

    public function content(): Content
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $link        = $frontendUrl . '/email/confirmed?token=' . urlencode($this->token)
                       . '&email=' . urlencode($this->user->email);

        return new Content(
            view:  'emails.confirm-email',
            with: [
                'user' => $this->user,
                'link' => $link,
            ],
        );
    }
}
