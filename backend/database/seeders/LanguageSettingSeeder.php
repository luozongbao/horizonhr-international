<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSettingSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('language_settings')->insert([
            [
                'code'        => 'en',
                'name'        => 'English',
                'native_name' => 'English',
                'flag'        => '🇬🇧',
                'is_active'   => true,
                'position'    => 1,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'zh_cn',
                'name'        => '中文简体',
                'native_name' => '简体中文',
                'flag'        => '🇨🇳',
                'is_active'   => true,
                'position'    => 2,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'code'        => 'th',
                'name'        => 'ภาษาไทย',
                'native_name' => 'ภาษาไทย',
                'flag'        => '🇹🇭',
                'is_active'   => true,
                'position'    => 3,
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    }
}
