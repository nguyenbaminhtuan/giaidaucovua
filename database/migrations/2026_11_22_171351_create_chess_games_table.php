<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chess_games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('chess_matches')->cascadeOnDelete();
            $table->unsignedInteger('game_number');
            $table->unsignedInteger('base_minutes');
            $table->unsignedInteger('increment_seconds')->default(0);
            $table->foreignId('white_player_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('black_player_id')->constrained('participants')->cascadeOnDelete();
            $table->string('result')->nullable(); // '1-0', '0.5-0.5', '0-1'
            $table->string('status')->default('pending'); // 'pending', 'completed'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chess_games');
    }
};
