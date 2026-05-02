<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $settings = [
            // Website General
            ['key' => 'site_name',                        'value' => 'HorizonHR',                                                 'type' => 'string',  'group' => 'website'],
            ['key' => 'site_name_zh_cn',                  'value' => '湖北豪睿国际人才服务有限公司',                               'type' => 'string',  'group' => 'website'],
            ['key' => 'site_name_en',                     'value' => 'HorizonHR International Talent Service',                    'type' => 'string',  'group' => 'website'],
            ['key' => 'site_name_th',                     'value' => 'บริการบุคลากรระหว่างประเทศ HorizonHR',                     'type' => 'string',  'group' => 'website'],
            ['key' => 'logo',                             'value' => '/assets/images/logo.png',                                   'type' => 'string',  'group' => 'website'],
            ['key' => 'logo_secondary',                   'value' => '/assets/images/logo-white.png',                             'type' => 'string',  'group' => 'website'],
            ['key' => 'favicon',                          'value' => '/assets/images/favicon.ico',                                'type' => 'string',  'group' => 'website'],
            ['key' => 'default_language',                 'value' => 'en',                                                        'type' => 'string',  'group' => 'website'],
            ['key' => 'contact_email',                    'value' => 'contact@horizonhr.com',                                     'type' => 'string',  'group' => 'website'],
            ['key' => 'contact_phone',                    'value' => '+86 27-8888-8888',                                          'type' => 'string',  'group' => 'website'],
            ['key' => 'contact_address',                  'value' => '',                                                          'type' => 'text',    'group' => 'website'],
            ['key' => 'copyright',                        'value' => '© 2026 Hubei Horizon International. All Rights Reserved.', 'type' => 'string',  'group' => 'website'],
            // SEO
            ['key' => 'seo_title',                        'value' => 'HorizonHR International Talent Service',                    'type' => 'string',  'group' => 'seo'],
            ['key' => 'seo_title_zh_cn',                  'value' => '湖北豪睿国际人才服务有限公司',                               'type' => 'string',  'group' => 'seo'],
            ['key' => 'seo_title_en',                     'value' => 'HorizonHR International Talent Service',                    'type' => 'string',  'group' => 'seo'],
            ['key' => 'seo_title_th',                     'value' => 'บริการบุคลากรระหว่างประเทศ HorizonHR',                     'type' => 'string',  'group' => 'seo'],
            ['key' => 'seo_description',                  'value' => '',                                                          'type' => 'text',    'group' => 'seo'],
            ['key' => 'seo_keywords',                     'value' => '',                                                          'type' => 'text',    'group' => 'seo'],
            ['key' => 'og_image',                         'value' => '/assets/images/og-image.jpg',                               'type' => 'string',  'group' => 'seo'],
            // Social Media
            ['key' => 'social_wechat',                    'value' => '',                                                          'type' => 'string',  'group' => 'social'],
            ['key' => 'social_whatsapp',                  'value' => '',                                                          'type' => 'string',  'group' => 'social'],
            ['key' => 'social_line',                      'value' => '',                                                          'type' => 'string',  'group' => 'social'],
            ['key' => 'social_facebook',                  'value' => '',                                                          'type' => 'string',  'group' => 'social'],
            ['key' => 'social_linkedin',                  'value' => '',                                                          'type' => 'string',  'group' => 'social'],
            // SMTP
            ['key' => 'smtp_driver',                      'value' => 'smtp',                                                      'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_host',                        'value' => '',                                                          'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_port',                        'value' => '587',                                                       'type' => 'number',  'group' => 'smtp'],
            ['key' => 'smtp_encryption',                  'value' => 'tls',                                                       'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_username',                    'value' => '',                                                          'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_password',                    'value' => '',                                                          'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_from_address',                'value' => '',                                                          'type' => 'string',  'group' => 'smtp'],
            ['key' => 'smtp_from_name',                   'value' => 'HorizonHR',                                                 'type' => 'string',  'group' => 'smtp'],
            // System
            ['key' => 'maintenance_mode',                 'value' => 'false',                                                     'type' => 'boolean', 'group' => 'system'],
            ['key' => 'debug_mode',                       'value' => 'false',                                                     'type' => 'boolean', 'group' => 'system'],
            ['key' => 'log_activity',                     'value' => 'true',                                                      'type' => 'boolean', 'group' => 'system'],
            ['key' => 'session_timeout',                  'value' => '120',                                                       'type' => 'number',  'group' => 'system'],
            ['key' => 'require_2fa_admin',                'value' => 'false',                                                     'type' => 'boolean', 'group' => 'system'],
            ['key' => 'allow_student_registration',       'value' => 'true',                                                      'type' => 'boolean', 'group' => 'system'],
            ['key' => 'allow_enterprise_registration',    'value' => 'true',                                                      'type' => 'boolean', 'group' => 'system'],
            ['key' => 'enable_video_interviews',          'value' => 'true',                                                      'type' => 'boolean', 'group' => 'system'],
            ['key' => 'enable_resume_upload',             'value' => 'true',                                                      'type' => 'boolean', 'group' => 'system'],
        ];

        foreach ($settings as &$row) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;
        }

        DB::table('settings')->insert($settings);
    }
}
