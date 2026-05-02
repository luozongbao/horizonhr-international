<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('creator_id')->comment('User who created interview (admin or enterprise)');
            $table->unsignedBigInteger('enterprise_id')->nullable();
            $table->unsignedBigInteger('student_id')->nullable();
            $table->unsignedBigInteger('job_id')->nullable();
            $table->string('title', 500);
            $table->dateTime('scheduled_at');
            $table->integer('duration')->default(30)->comment('Duration in minutes');
            $table->string('room_id', 100)->nullable()->comment('WebRTC room identifier');
            $table->string('room_token', 500)->nullable()->comment('Token for room access');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->boolean('reminder_sent')->default(false);
            $table->timestamps();

            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('set null');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('set null');
            $table->index('creator_id');
            $table->index('student_id');
            $table->index('enterprise_id');
            $table->index('status');
            $table->index('scheduled_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
