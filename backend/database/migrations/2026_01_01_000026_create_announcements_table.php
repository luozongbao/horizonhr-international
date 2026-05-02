<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title_zh_cn', 500);
            $table->string('title_en', 500);
            $table->string('title_th', 500);
            $table->longText('content_zh_cn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_th')->nullable();
            $table->string('type', 100)->nullable();
            $table->enum('target', ['all', 'students', 'enterprises'])->default('all');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
