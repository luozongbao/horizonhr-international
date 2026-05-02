<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enterprises', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->string('company_name', 500);
            $table->string('industry', 200)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('logo_key', 500)->nullable(); // OSS storage key for deletion
            $table->enum('scale', ['small', 'medium', 'large', 'enterprise'])->nullable();
            $table->text('description')->nullable();
            $table->string('website', 500)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 50)->nullable();
            $table->boolean('verified')->default(false);
            $table->enum('prefer_lang', ['zh_cn', 'en', 'th'])->default('en');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('industry');
            $table->index('verified');
            $table->index('prefer_lang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enterprises');
    }
};
