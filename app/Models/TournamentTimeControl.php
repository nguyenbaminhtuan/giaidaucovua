<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TournamentTimeControl extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'group_id',
        'game_number',
        'base_minutes',
        'increment_seconds',
    ];

    protected $casts = [
        'game_number' => 'integer',
        'base_minutes' => 'integer',
        'increment_seconds' => 'integer',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TournamentGroup::class, 'group_id');
    }

    public function getFormattedAttribute(): string
    {
        return "{$this->base_minutes}m + {$this->increment_seconds}s";
    }
}
