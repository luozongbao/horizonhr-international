<?php

namespace App\Mail;

use App\Models\Seminar;
use App\Models\SeminarRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SeminarReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Seminar             $seminar,
        public readonly SeminarRegistration $registration,
        public readonly string              $lang = 'en',
    ) {}

    public function envelope(): Envelope
    {
        $title = ($this->lang === 'zh_cn')
            ? ($this->seminar->title_zh_cn ?? $this->seminar->title_en)
            : ($this->seminar->title_en ?? $this->seminar->title_zh_cn);

        return new Envelope(
            subject: 'Seminar Reminder: ' . $title,
        );
    }

    public function content(): Content
    {
        $registrationCount = $this->seminar->registrations()->count();
        $minutesUntilStart = max(0, (int) now()->diffInMinutes($this->seminar->starts_at, false));

        return new Content(
            view: 'emails.seminar-reminder',
            with: [
                'seminar'           => $this->seminar,
                'registration'      => $this->registration,
                'registrationCount' => $registrationCount,
                'minutesUntilStart' => $minutesUntilStart,
                'seminarDay'        => $this->seminar->starts_at->format('d'),
                'seminarMonth'      => $this->seminar->starts_at->format('M'),
                'seminarTime'       => $this->seminar->starts_at->format('H:i'),
                'watchUrl'          => rtrim(config('app.frontend_url'), '/') . '/seminars/' . $this->seminar->id,
                'lang'              => $this->lang,
            ],
        );
    }
}
