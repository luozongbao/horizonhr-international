<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('talent_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->string('display_name');
            $table->string('major')->nullable();
            $table->string('education', 100)->nullable();
            $table->string('university')->nullable();
            $table->json('languages')->nullable()->comment('Array of language skills');
            $table->json('skills')->nullable()->comment('Array of skills');
            $table->json('work_experience')->nullable()->comment('Array of work experiences');
            $table->string('job_intention', 500)->nullable();
            $table->enum('status', ['hidden', 'visible', 'featured'])->default('hidden');
            $table->timestamps();

            $table->foreign('student_id')->references('id')->on('students')->onDelete('cascade');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('talent_cards');
    }
};
