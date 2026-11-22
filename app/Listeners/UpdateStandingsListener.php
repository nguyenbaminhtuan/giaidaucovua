<?php

namespace App\Listeners;

use App\Events\MatchResultConfirmed;
use App\Services\Standings\StandingsCalculatorService;

class UpdateStandingsListener
{
    public function __construct(
        protected StandingsCalculatorService $calculator
    ) {}

    public function handle(MatchResultConfirmed $event): void
    {
        $this->calculator->recalculateForMatch($event->match);
    }
}
