<?php

namespace App\Events;

use App\Models\ChessMatch;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MatchResultConfirmed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public ChessMatch $match
    ) {}
}
