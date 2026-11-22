<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chess_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('player1_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('player2_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->boolean('is_bye')->default(false);
            $table->decimal('player1_score', 4, 1)->default(0.0);
            $table->decimal('player2_score', 4, 1)->default(0.0);
            $table->string('status')->default('pending'); // 'pending', 'completed'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chess_matches');
    }
};
