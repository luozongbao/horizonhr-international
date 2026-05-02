<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->unsignedBigInteger('user_id')->nullable()->comment('Null if anonymous user');
            $table->string('user_name');
            $table->string('content', 500)->comment('Danmu message content (max 100 chars)');
            $table->string('color', 20)->default('#FFFFFF')->comment('Message color in hex');
            $table->enum('position', ['scroll', 'top', 'bottom'])->default('scroll')->comment('Danmu position');
            $table->integer('font_size')->default(18)->comment('Font size in px');
            $table->dateTime('send_at');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('seminar_id')->references('id')->on('seminars')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->index('seminar_id');
            $table->index('send_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_messages');
    }
};
