<?php

namespace App\Console\Commands;

use App\Jobs\SendSeminarReminderJob;
use App\Models\Seminar;
use Illuminate\Console\Command;

class SendSeminarReminders extends Command
{
    protected $signature   = 'seminars:send-reminders';
    protected $description = 'Dispatch reminder emails to registrants for seminars starting in ~15 minutes.';

    public function handle(): int
    {
        // Find seminars starting in 14–16 minutes that have not yet sent reminders.
        $seminars = Seminar::where('status', 'scheduled')
            ->where('reminder_sent', false)
            ->whereBetween('starts_at', [
                now()->addMinutes(14),
                now()->addMinutes(16),
            ])
            ->with('registrations')
            ->get();

        if ($seminars->isEmpty()) {
            return self::SUCCESS;
        }

        foreach ($seminars as $seminar) {
            foreach ($seminar->registrations as $registration) {
                SendSeminarReminderJob::dispatch($seminar, $registration);
            }

            $seminar->update(['reminder_sent' => true]);

            $this->info("Dispatched reminders for seminar #{$seminar->id}: {$seminar->title_en}");
        }

        return self::SUCCESS;
    }
}
