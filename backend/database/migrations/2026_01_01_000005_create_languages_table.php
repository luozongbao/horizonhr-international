<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('zh_cn')->nullable();
            $table->text('en')->nullable();
            $table->text('th')->nullable();
            $table->timestamps();

            $table->index('key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('languages');
    }
};
