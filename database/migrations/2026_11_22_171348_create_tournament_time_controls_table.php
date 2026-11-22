<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_time_controls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('game_number'); // 1, 2, ...
            $table->unsignedInteger('base_minutes'); // e.g., 5, 15
            $table->unsignedInteger('increment_seconds')->default(0); // e.g., 2
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_time_controls');
    }
};
