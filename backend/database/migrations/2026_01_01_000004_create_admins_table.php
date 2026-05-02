<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->enum('prefer_lang', ['zh_cn', 'en', 'th'])->default('en');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('prefer_lang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
