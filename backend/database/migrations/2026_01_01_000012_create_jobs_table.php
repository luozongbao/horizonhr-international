<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('enterprise_id');
            $table->string('title', 500);
            $table->text('description');
            $table->text('requirements')->nullable();
            $table->string('location')->nullable();
            $table->integer('salary_min')->nullable();
            $table->integer('salary_max')->nullable();
            $table->string('salary_currency', 10)->default('CNY');
            $table->enum('job_type', ['full_time', 'part_time', 'contract', 'internship']);
            $table->enum('status', ['draft', 'published', 'closed', 'expired'])->default('draft');
            $table->dateTime('published_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->integer('view_count')->default(0);
            $table->timestamps();

            $table->foreign('enterprise_id')->references('id')->on('enterprises')->onDelete('cascade');
            $table->index('enterprise_id');
            $table->index('status');
            $table->index('location');
            $table->index('job_type');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs');
    }
};
