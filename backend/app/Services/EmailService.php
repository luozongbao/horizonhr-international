<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;

/**
 * Dynamic Email Service.
 *
 * Reads SMTP credentials from DB settings at send-time, overrides the Laravel
 * mail config with Config::set(), purges the cached mailer, then sends.
 *
 * This approach allows an admin to update SMTP settings in the UI and have
 * all subsequent emails (including queued jobs) use the new credentials,
 * without restarting the queue worker.
 *
 * Fallback: if a DB setting is empty, the value from config/mail.php (env) is used.
 */
class EmailService
{
    /**
     * Send a Mailable to a single recipient using DB SMTP settings.
     *
     * @param  Mailable  $mailable
     * @param  string    $toEmail   Recipient email address
     * @param  string    $toName    Recipient display name (optional)
     */
    public function send(Mailable $mailable, string $toEmail, string $toName = ''): void
    {
        $this->applySmtpConfig();

        Mail::to($toEmail, $toName ?: null)->send($mailable);
    }

    /**
     * Send a Mailable to multiple recipients using DB SMTP settings.
     *
     * @param  Mailable              $mailable
     * @param  array<string|int, string>  $recipients  [email] or [email => name, ...]
     */
    public function sendToMany(Mailable $mailable, array $recipients): void
    {
        $this->applySmtpConfig();

        Mail::to($recipients)->send($mailable);
    }

    /**
     * Apply DB-stored SMTP settings to the Laravel mail config.
     * Purges the cached smtp mailer so the next send() builds a fresh transport.
     */
    private function applySmtpConfig(): void
    {
        $host       = Setting::get('smtp_host')        ?: config('mail.mailers.smtp.host',       'localhost');
        $port       = (int)(Setting::get('smtp_port') ?: config('mail.mailers.smtp.port',        587));
        $encryption = Setting::get('smtp_encryption')  ?: config('mail.mailers.smtp.encryption', null);
        $username   = Setting::get('smtp_username')    ?: config('mail.mailers.smtp.username',   null);
        $password   = Setting::get('smtp_password')    ?: config('mail.mailers.smtp.password',   null);
        $fromAddr   = Setting::get('smtp_from_address') ?: config('mail.from.address', 'noreply@horizonhr.com');
        $fromName   = Setting::get('smtp_from_name')   ?: config('mail.from.name',    'HorizonHR');

        Config::set([
            'mail.default'                 => 'smtp',
            'mail.mailers.smtp.host'       => $host,
            'mail.mailers.smtp.port'       => $port,
            'mail.mailers.smtp.encryption' => $encryption ?: null,
            'mail.mailers.smtp.username'   => $username,
            'mail.mailers.smtp.password'   => $password,
            'mail.from.address'            => $fromAddr,
            'mail.from.name'               => $fromName,
        ]);

        // Purge the cached smtp mailer so it rebuilds with the new config.
        // This is safe to call even if the mailer hasn't been resolved yet.
        Mail::purge('smtp');
    }
}
