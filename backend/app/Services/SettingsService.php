<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY_ALL    = 'settings:all';
    private const CACHE_KEY_PUBLIC = 'settings:public';
    private const CACHE_TTL        = 3600; // 1 hour

    /**
     * Keys exposed to unauthenticated public endpoints.
     * SMTP credentials and system flags are intentionally excluded.
     */
    private const PUBLIC_KEYS = [
        'site_name',
        'site_name_zh_cn',
        'site_name_en',
        'site_name_th',
        'logo',
        'logo_secondary',
        'favicon',
        'default_language',
        'contact_email',
        'contact_phone',
        'contact_address',
        'copyright',
        'social_wechat',
        'social_whatsapp',
        'social_line',
        'social_facebook',
        'social_linkedin',
    ];

    public function getPublicSettings(): array
    {
        return Cache::remember(self::CACHE_KEY_PUBLIC, self::CACHE_TTL, function () {
            return Setting::whereIn('key', self::PUBLIC_KEYS)
                ->pluck('value', 'key')
                ->toArray();
        });
    }

    public function getAllGrouped(): array
    {
        return Cache::remember(self::CACHE_KEY_ALL, self::CACHE_TTL, function () {
            $grouped = [];

            Setting::all()->each(function (Setting $setting) use (&$grouped) {
                $grouped[$setting->group][$setting->key] = $setting->value;
            });

            return $grouped;
        });
    }

    /**
     * Bulk-update settings by key => value map.
     * Only updates keys that already exist in the DB — prevents arbitrary key injection.
     */
    public function bulkUpdate(array $data): void
    {
        foreach ($data as $key => $value) {
            Setting::where('key', $key)->update(['value' => $value === null ? '' : (string) $value]);
        }

        $this->clearCache();
    }

    /**
     * Test the SMTP connection using current DB settings.
     * Opens a raw TCP socket — does NOT send any email.
     *
     * @return array{success: bool, message: string, code?: string}
     */
    public function testSmtp(): array
    {
        $host = Setting::get('smtp_host', '');
        $port = (int) Setting::get('smtp_port', 587);

        if (empty($host)) {
            return [
                'success' => false,
                'code'    => 'SMTP_NOT_CONFIGURED',
                'message' => 'SMTP host is not configured.',
            ];
        }

        $errno  = 0;
        $errstr = '';

        // phpcs:ignore — intentional error suppression for socket test
        $connection = @fsockopen($host, $port, $errno, $errstr, 5);

        if ($connection) {
            fclose($connection);

            return ['success' => true, 'message' => "SMTP connection to {$host}:{$port} successful."];
        }

        return [
            'success' => false,
            'code'    => 'SMTP_CONNECTION_FAILED',
            'message' => "Connection to {$host}:{$port} failed: {$errstr} (errno: {$errno})",
        ];
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY_ALL);
        Cache::forget(self::CACHE_KEY_PUBLIC);
    }
}
