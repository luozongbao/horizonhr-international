<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seminar_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('seminar_id');
            $table->string('email');
            $table->string('name');
            $table->string('phone', 50)->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->comment('Null if not logged-in user');
            $table->boolean('reminder_sent')->default(false);
            $table->dateTime('reminder_sent_at')->nullable();
            $table->timestamp('registered_at')->useCurrent();

            $table->foreign('seminar_id')->references('id')->on('seminars')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['seminar_id', 'email']);
            $table->index('seminar_id');
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seminar_registrations');
    }
};
