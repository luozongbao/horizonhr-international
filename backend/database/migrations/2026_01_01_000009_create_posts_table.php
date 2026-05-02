<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('page_id')->nullable();
            $table->string('title_zh_cn', 500);
            $table->string('title_en', 500);
            $table->string('title_th', 500);
            $table->longText('content_zh_cn')->nullable();
            $table->longText('content_en')->nullable();
            $table->longText('content_th')->nullable();
            $table->string('meta_title_zh_cn', 500)->nullable();
            $table->string('meta_title_en', 500)->nullable();
            $table->string('meta_title_th', 500)->nullable();
            $table->text('meta_desc_zh_cn')->nullable();
            $table->text('meta_desc_en')->nullable();
            $table->text('meta_desc_th')->nullable();
            $table->enum('category', ['company_news', 'industry_news', 'study_abroad', 'recruitment']);
            $table->string('thumbnail', 500)->nullable();
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->integer('view_count')->default(0);
            $table->dateTime('published_at')->nullable();
            $table->timestamps();

            $table->foreign('page_id')->references('id')->on('pages')->onDelete('set null');
            $table->index('category');
            $table->index('status');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
