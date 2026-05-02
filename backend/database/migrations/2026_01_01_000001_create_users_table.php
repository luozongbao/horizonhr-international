<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->enum('role', ['admin', 'student', 'enterprise'])->default('student');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('status', ['pending', 'active', 'suspended', 'deleted'])->default('pending');
            $table->enum('enterprise_status', ['pending', 'enterprise_verified'])->nullable();
            $table->enum('prefer_lang', ['zh_cn', 'en', 'th'])->default('en');
            $table->boolean('email_verified')->default(false);
            $table->dateTime('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();
            $table->timestamps();

            $table->index('email');
            $table->index(['role', 'status']);
            $table->index('enterprise_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
