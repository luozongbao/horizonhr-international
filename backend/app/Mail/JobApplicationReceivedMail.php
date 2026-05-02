<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Job;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JobApplicationReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Application $application,
        public readonly Job         $job,
        public readonly Student     $student,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New Job Application Received — ' . $this->job->title);
    }

    public function content(): Content
    {
        $frontendUrl    = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $applicationsUrl = $frontendUrl . '/enterprise/applications/' . $this->application->id;

        return new Content(
            view: 'emails.job-application-received',
            with: [
                'application'     => $this->application,
                'job'             => $this->job,
                'student'         => $this->student,
                'applicationsUrl' => $applicationsUrl,
            ],
        );
    }
}
