<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User   $user,
        public readonly string $token,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Password Reset — HorizonHR');
    }

    public function content(): Content
    {
        $frontendUrl = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $link        = $frontendUrl . '/password/reset?token=' . urlencode($this->token)
                       . '&email=' . urlencode($this->user->email);

        return new Content(
            view:  'emails.password-reset',
            with: [
                'user' => $this->user,
                'link' => $link,
            ],
        );
    }
}
