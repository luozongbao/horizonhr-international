<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_recordings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id')->unique();
            $table->string('title', 500);
            $table->string('video_url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->integer('duration_sec');
            $table->json('playback_speeds')->comment('Supported playback speeds');
            $table->string('default_speed', 10)->default('1x')->comment('Default playback speed');
            $table->integer('view_count')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('seminar_id')->references('id')->on('seminars')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_recordings');
    }
};
