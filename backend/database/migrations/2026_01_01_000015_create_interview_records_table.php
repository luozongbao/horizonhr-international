<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interview_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interview_id');
            $table->unsignedBigInteger('participant_id')->comment('User or student participant');
            $table->enum('participant_type', ['admin', 'enterprise', 'student']);
            $table->dateTime('joined_at');
            $table->dateTime('left_at')->nullable();
            $table->integer('duration_sec')->nullable();
            $table->string('connection_quality', 50)->nullable()->comment('good, medium, poor');
            $table->text('notes')->nullable();
            $table->enum('result', ['pass', 'fail', 'pending', 'no_show'])->nullable();
            $table->integer('rating')->nullable()->comment('1-5 rating');
            $table->string('recording_url', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('interview_id')->references('id')->on('interviews')->onDelete('cascade');
            $table->index('interview_id');
            $table->index('participant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interview_records');
    }
};
