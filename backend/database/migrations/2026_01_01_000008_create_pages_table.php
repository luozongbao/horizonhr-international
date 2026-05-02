<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
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
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->enum('type', ['page', 'announcement'])->default('page');
            $table->integer('order_num')->default(0);
            $table->timestamps();

            $table->index('slug');
            $table->index('status');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
