<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'name',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class, 'group_id');
    }

    public function timeControls(): HasMany
    {
        return $this->hasMany(TournamentTimeControl::class, 'group_id')->orderBy('game_number');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class, 'group_id')->orderBy('round_number');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class, 'group_id');
    }
}
