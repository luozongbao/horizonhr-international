<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_authentications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->enum('provider', ['google', 'facebook', 'linkedin', 'wechat']);
            $table->string('provider_id')->comment('User ID from the social provider');
            $table->string('provider_email')->nullable()->comment('Email from provider');
            $table->string('provider_name')->nullable()->comment('Display name from provider');
            $table->string('provider_avatar', 500)->nullable()->comment('Avatar URL from provider');
            $table->text('access_token')->nullable()->comment('Encrypted access token');
            $table->text('refresh_token')->nullable()->comment('Encrypted refresh token');
            $table->dateTime('token_expires_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['provider', 'provider_id']);
            $table->unique(['user_id', 'provider']);
            $table->index('provider');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_authentications');
    }
};
