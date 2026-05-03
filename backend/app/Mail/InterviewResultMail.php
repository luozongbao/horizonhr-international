<?php

namespace App\Mail;

use App\Models\Interview;
use App\Models\InterviewRecord;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewResultMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Interview       $interview,
        public readonly InterviewRecord $record,
        public readonly Student         $student,
        public readonly string          $lang = 'en',
    ) {}

    public function envelope(): Envelope
    {
        $result = ucfirst(str_replace('_', ' ', $this->record->result ?? 'updated'));
        return new Envelope(subject: "Interview Result — {$result}: {$this->interview->title}");
    }

    public function content(): Content
    {
        $frontendUrl     = rtrim(config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:5173')), '/');
        $dashboardUrl    = $frontendUrl . '/student/interviews';

        return new Content(
            view: 'emails.interview-result',
            with: [
                'interview'    => $this->interview,
                'record'       => $this->record,
                'student'      => $this->student,
                'dashboardUrl' => $dashboardUrl,
                'lang'         => $this->lang,
            ],
        );
    }
}
