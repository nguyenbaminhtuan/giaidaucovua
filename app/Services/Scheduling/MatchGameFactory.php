<?php

namespace App\Services\Scheduling;

use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\TournamentTimeControl;
use Illuminate\Support\Collection;

class MatchGameFactory
{
    /**
     * Create a ChessMatch along with alternating ChessGame records.
     *
     * @param  Round  $round
     * @param  Participant  $player1
     * @param  Participant|null  $player2
     * @param  bool  $isBye
     * @param  Collection<int, TournamentTimeControl>|array  $timeControls
     * @return ChessMatch
     */
    public function createMatch(
        Round $round,
        Participant $player1,
        ?Participant $player2 = null,
        bool $isBye = false,
        $timeControls = []
    ): ChessMatch {
        if ($isBye || $player2 === null) {
            return ChessMatch::create([
                'round_id' => $round->id,
                'player1_id' => $player1->id,
                'player2_id' => null,
                'is_bye' => true,
                'player1_score' => 1.0,
                'player2_score' => 0.0,
                'status' => 'completed',
            ]);
        }

        $match = ChessMatch::create([
            'round_id' => $round->id,
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'is_bye' => false,
            'player1_score' => 0.0,
            'player2_score' => 0.0,
            'status' => 'pending',
        ]);

        $tcCollection = collect($timeControls);

        // Fallback default: 1 game with 5m+0s if none configured
        if ($tcCollection->isEmpty()) {
            $tcCollection = collect([
                (object) ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 0],
            ]);
        }

        foreach ($tcCollection as $tc) {
            $gameNumber = (int) (is_array($tc) ? $tc['game_number'] : $tc->game_number);
            $baseMinutes = (int) (is_array($tc) ? $tc['base_minutes'] : $tc->base_minutes);
            $incrementSec = (int) (is_array($tc) ? ($tc['increment_seconds'] ?? 0) : ($tc->increment_seconds ?? 0));
            $isOdd = ($gameNumber % 2 !== 0);

            ChessGame::create([
                'match_id' => $match->id,
                'game_number' => $gameNumber,
                'base_minutes' => $baseMinutes,
                'increment_seconds' => $incrementSec,
                'white_player_id' => $isOdd ? $player1->id : $player2->id,
                'black_player_id' => $isOdd ? $player2->id : $player1->id,
                'result' => null,
                'status' => 'pending',
            ]);
        }

        return $match;
    }
}
