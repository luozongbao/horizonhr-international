<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->unsignedBigInteger('trtc_room_id')
                  ->nullable()
                  ->after('room_id')
                  ->comment('Numeric TRTC room ID (uint32) generated at interview creation');
        });
    }

    public function down(): void
    {
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropColumn('trtc_room_id');
        });
    }
};
