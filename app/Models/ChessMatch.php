<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChessMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'round_id',
        'player1_id',
        'player2_id',
        'is_bye',
        'player1_score',
        'player2_score',
        'status',
    ];

    protected $casts = [
        'is_bye' => 'boolean',
        'player1_score' => 'float',
        'player2_score' => 'float',
    ];

    public function round(): BelongsTo
    {
        return $this->belongsTo(Round::class);
    }

    public function player1(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'player1_id');
    }

    public function player2(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'player2_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(ChessGame::class, 'match_id')->orderBy('game_number');
    }

    public function recalculateScores(): void
    {
        if ($this->is_bye) {
            $this->player1_score = 1.0;
            $this->player2_score = 0.0;
            $this->save();
            return;
        }

        $p1 = 0.0;
        $p2 = 0.0;

        foreach ($this->games as $game) {
            if ($game->result === '1-0') {
                if ($game->white_player_id === $this->player1_id) {
                    $p1 += 1.0;
                } else {
                    $p2 += 1.0;
                }
            } elseif ($game->result === '0-1') {
                if ($game->black_player_id === $this->player1_id) {
                    $p1 += 1.0;
                } else {
                    $p2 += 1.0;
                }
            } elseif ($game->result === '0.5-0.5') {
                $p1 += 0.5;
                $p2 += 0.5;
            }
        }

        $this->player1_score = $p1;
        $this->player2_score = $p2;
        $this->save();
    }

    public function areAllGamesFinished(): bool
    {
        if ($this->is_bye) {
            return true;
        }

        if ($this->games->isEmpty()) {
            return false;
        }

        return $this->games->every(fn (ChessGame $game) => ! empty($game->result));
    }

    public function isTied(): bool
    {
        return abs($this->player1_score - $this->player2_score) < 0.001;
    }

    public function getWinner(): ?Participant
    {
        if ($this->isTied()) {
            return null;
        }

        return $this->player1_score > $this->player2_score ? $this->player1 : $this->player2;
    }

    public function getLoser(): ?Participant
    {
        if ($this->isTied()) {
            return null;
        }

        return $this->player1_score > $this->player2_score ? $this->player2 : $this->player1;
    }
}
