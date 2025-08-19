<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pomodoros', function (Blueprint $table) {
            $table->timestamp('expected_end_at')->nullable()->after('ended_at');
            $table->integer('accumulated_seconds')->default(0)->after('expected_end_at');
            $table->timestamp('paused_at')->nullable()->after('accumulated_seconds');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pomodoros', function (Blueprint $table) {
            $table->dropColumn(['expected_end_at', 'accumulated_seconds', 'paused_at']);

        });
    }
};
