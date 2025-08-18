<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pomodoros', function (Blueprint $table) {
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])
                ->default('medium')
                ->after('status');
            $table->string('project_name')->nullable()->after('priority');
            $table->json('tags')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pomodoros');
    }
};
