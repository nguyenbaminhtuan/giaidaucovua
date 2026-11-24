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
        'advancing_count',
        'bonus_top1',
        'bonus_top2',
        'bonus_top3',
        'playoff_title',
        'playoff_description',
        'status',
    ];

    protected $casts = [
        'advancing_count' => 'integer',
        'bonus_top1' => 'float',
        'bonus_top2' => 'float',
        'bonus_top3' => 'float',
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

    public function groupRounds(): HasMany
    {
        return $this->hasMany(Round::class)->where('stage', 'group_stage')->orderBy('round_number');
    }

    public function knockoutRounds(): HasMany
    {
        return $this->hasMany(Round::class)->where('stage', 'knockout')->orderBy('round_number');
    }

    public function stage2Rounds(): HasMany
    {
        return $this->hasMany(Round::class)->where('stage', 'stage2_round_robin')->orderBy('round_number');
    }

    public function stepladderRounds(): HasMany
    {
        return $this->hasMany(Round::class)->where('stage', 'stepladder')->orderBy('round_number');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(Standing::class);
    }
}
