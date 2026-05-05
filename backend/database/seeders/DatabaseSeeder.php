<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            LanguageSettingSeeder::class,
            LanguageSeeder::class,
            SettingSeeder::class,
            AdminSeeder::class,
            PageSeeder::class,
            DummyDataSeeder::class,
        ]);
    }
}
