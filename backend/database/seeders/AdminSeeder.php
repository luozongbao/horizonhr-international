<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->warn(
            '⚠  Default admin created: admin@horizonhr.com / Admin@12345 — CHANGE THIS PASSWORD IMMEDIATELY after first login!'
        );

        $user = User::create([
            'role'           => 'admin',
            'email'          => 'admin@horizonhr.com',
            'password'       => Hash::make('Admin@12345'),
            'status'         => 'active',
            'email_verified' => true,
            'prefer_lang'    => 'en',
        ]);

        Admin::create([
            'user_id'     => $user->id,
            'name'        => 'System Admin',
            'prefer_lang' => 'en',
        ]);
    }
}
