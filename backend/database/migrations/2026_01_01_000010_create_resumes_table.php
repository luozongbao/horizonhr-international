<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resumes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('file_path', 500);
            $table->string('file_name');
            $table->enum('file_type', ['pdf', 'doc', 'docx', 'jpg', 'png']);
            $table->integer('file_size')->comment('Size in bytes');
            $table->enum('visibility', ['admin_only', 'enterprise_visible', 'public'])->default('enterprise_visible');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');
            $table->index('student_id');
            $table->index('visibility');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resumes');
    }
};
