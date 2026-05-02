<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusChangedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly Job         $job,
    ) {}

    public function envelope(): Envelope
    {
        $status = ucfirst($this->application->status);
        return new Envelope(subject: "Your Application Has Been {$status} — {$this->job->title}");
    }

    public function content(): Content
    {
        $frontendUrl     = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $applicationsUrl = $frontendUrl . '/student/applications';

        return new Content(
            view: 'emails.application-status-changed',
            with: [
                'application'     => $this->application,
                'job'             => $this->job,
                'enterprise'      => $this->job->enterprise,
                'applicationsUrl' => $applicationsUrl,
            ],
        );
    }
}
