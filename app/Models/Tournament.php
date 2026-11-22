<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'format',
        'status',
    ];

    public function groups(): HasMany
    {
        return $this->hasMany(TournamentGroup::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function timeControls(): HasMany
    {
        return $this->hasMany(TournamentTimeControl::class)->orderBy('game_number');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(Round::class)->orderBy('round_number');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }
}
