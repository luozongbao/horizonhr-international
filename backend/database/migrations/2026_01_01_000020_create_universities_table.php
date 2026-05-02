<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('universities', function (Blueprint $table) {
            $table->id();
            $table->string('name_zh_cn', 500);
            $table->string('name_en', 500);
            $table->string('name_th', 500)->nullable();
            $table->string('logo', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('location')->nullable();
            $table->string('location_city', 100)->nullable();
            $table->string('location_region', 100)->nullable();
            $table->string('website', 500)->nullable();
            $table->text('description')->nullable();
            $table->json('majors')->nullable()->comment('Array of offered majors');
            $table->json('program_types')->nullable()->comment('Array of program types: vocational, bachelor, master, language');
            $table->integer('established_year')->nullable();
            $table->integer('ranking')->nullable();
            $table->timestamps();

            $table->index('location');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('universities');
    }
};
