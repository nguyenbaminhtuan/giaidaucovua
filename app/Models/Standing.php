<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Standing extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'group_id',
        'stage',
        'participant_id',
        'played',
        'won',
        'drawn',
        'lost',
        'points',
        'bonus_points',
        'tie_break_score',
    ];

    protected $casts = [
        'played' => 'integer',
        'won' => 'integer',
        'drawn' => 'integer',
        'lost' => 'integer',
        'points' => 'float',
        'bonus_points' => 'float',
        'tie_break_score' => 'float',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TournamentGroup::class, 'group_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function getTotalPointsAttribute(): float
    {
        return (float) ($this->points + $this->bonus_points);
    }
}
