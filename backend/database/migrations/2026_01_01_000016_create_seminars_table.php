<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminars', function (Blueprint $table) {
            $table->id();
            $table->string('title_zh_cn', 500);
            $table->string('title_en', 500);
            $table->string('title_th', 500);
            $table->text('desc_zh_cn')->nullable();
            $table->text('desc_en')->nullable();
            $table->text('desc_th')->nullable();
            $table->string('speaker_name');
            $table->string('speaker_title')->nullable();
            $table->text('speaker_bio')->nullable();
            $table->string('speaker_avatar', 500)->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->string('stream_url', 500)->nullable();
            $table->string('stream_key')->nullable();
            $table->enum('target_audience', ['students', 'enterprises', 'both'])->default('both');
            $table->enum('status', ['scheduled', 'live', 'ended', 'cancelled'])->default('scheduled');
            $table->enum('permission', ['public', 'registered'])->default('registered');
            $table->integer('max_viewers')->nullable();
            $table->integer('current_viewers')->default(0);
            $table->integer('max_concurrent_viewers')->default(10000)->comment('System capacity: supports 10,000+ concurrent viewers');
            $table->dateTime('starts_at');
            $table->integer('duration_min')->default(60);
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('target_audience');
            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminars');
    }
};
