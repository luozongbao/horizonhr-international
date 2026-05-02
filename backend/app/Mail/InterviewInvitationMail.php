<?php

namespace App\Mail;

use App\Models\Interview;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Interview $interview,
        public readonly Student   $student,
        public readonly string    $joinUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Interview Invitation: ' . $this->interview->title . ' — HorizonHR');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-invitation',
            with: [
                'interview' => $this->interview,
                'student'   => $this->student,
                'joinUrl'   => $this->joinUrl,
            ],
        );
    }
}
