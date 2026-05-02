<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('name');
            $table->string('nationality', 100)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('avatar', 500)->nullable();
            $table->string('avatar_key', 500)->nullable(); // OSS storage key for deletion
            $table->date('birth_date')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->text('address')->nullable();
            $table->text('bio')->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('prefer_lang', ['zh_cn', 'en', 'th'])->default('en');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('nationality');
            $table->index('verified');
            $table->index('prefer_lang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
