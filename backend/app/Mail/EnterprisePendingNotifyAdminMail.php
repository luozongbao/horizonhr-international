<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EnterprisePendingNotifyAdminMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly User $user, public readonly string $lang = 'en') {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Enterprise Registration Pending Review — HorizonHR');
    }

    public function content(): Content
    {
        $frontendUrl    = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $adminReviewUrl = $frontendUrl . '/admin/users?status=pending&role=enterprise';
        $enterprise     = $this->user->enterprise;

        return new Content(
            view: 'emails.enterprise-pending-notify-admin',
            with: [
                'user'           => $this->user,
                'enterprise'     => $enterprise,
                'adminReviewUrl' => $adminReviewUrl,
                'lang'           => $this->lang,
            ],
        );
    }
}
