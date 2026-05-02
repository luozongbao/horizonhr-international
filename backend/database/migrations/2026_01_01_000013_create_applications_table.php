<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('job_id');
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('resume_id')->nullable();
            $table->text('cover_letter')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'interviewed', 'accepted', 'rejected', 'withdrawn'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('applied_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();

            $table->foreign('job_id')->references('id')->on('jobs')->onDelete('cascade');
            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('resume_id')->references('id')->on('resumes')->onDelete('set null');
            $table->unique(['job_id', 'student_id']);
            $table->index('job_id');
            $table->index('student_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
