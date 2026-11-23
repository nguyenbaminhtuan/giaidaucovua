<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChessGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'game_number',
        'base_minutes',
        'increment_seconds',
        'white_player_id',
        'black_player_id',
        'result',
        'status',
        'is_tie_break',
    ];

    protected $casts = [
        'game_number' => 'integer',
        'base_minutes' => 'integer',
        'increment_seconds' => 'integer',
        'is_tie_break' => 'boolean',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(ChessMatch::class, 'match_id');
    }

    public function whitePlayer(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'white_player_id');
    }

    public function blackPlayer(): BelongsTo
    {
        return $this->belongsTo(Participant::class, 'black_player_id');
    }

    public function getTimeControlLabelAttribute(): string
    {
        $prefix = $this->is_tie_break ? '⚡ Tie-break: ' : '';
        return "{$prefix}{$this->base_minutes}m + {$this->increment_seconds}s";
    }
}
