<?php

namespace App\Services\Scheduling;

use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SwissSchedulerService
{
    public function __construct(
        protected MatchGameFactory $matchGameFactory
    ) {}

    /**
     * Generate Swiss pairings for a specific round.
     *
     * @param  Tournament  $tournament
     * @param  int  $roundNumber
     * @return Round|null
     */
    public function generateNextRound(Tournament $tournament, int $roundNumber): ?Round
    {
        return DB::transaction(function () use ($tournament, $roundNumber) {
            $tournament->load(['participants', 'timeControls', 'rounds.matches.games']);
            $participants = $tournament->participants->values();

            if ($participants->count() < 2) {
                return null;
            }

            // Map previous pairings and byes
            $playedMap = [];
            $playersWithBye = [];
            $whiteCount = [];

            foreach ($participants as $p) {
                $whiteCount[$p->id] = 0;
            }

            foreach ($tournament->rounds as $pastRound) {
                foreach ($pastRound->matches as $m) {
                    if ($m->is_bye) {
                        $playersWithBye[$m->player1_id] = true;
                    } elseif ($m->player2_id) {
                        $key = min($m->player1_id, $m->player2_id) . '_' . max($m->player1_id, $m->player2_id);
                        $playedMap[$key] = true;
                    }

                    foreach ($m->games as $g) {
                        if (isset($whiteCount[$g->white_player_id])) {
                            $whiteCount[$g->white_player_id]++;
                        }
                    }
                }
            }

            // Rank players by current points descending
            $standings = Standing::where('tournament_id', $tournament->id)
                ->pluck('points', 'participant_id');

            $rankedPlayers = $participants->sortByDesc(function (Participant $p) use ($standings) {
                return $standings[$p->id] ?? 0.0;
            })->values()->all();

            $pool = $rankedPlayers;
            $byePlayer = null;

            // Odd players: assign Bye to lowest-ranked player without a prior bye
            if (count($pool) % 2 !== 0) {
                for ($i = count($pool) - 1; $i >= 0; $i--) {
                    if (! isset($playersWithBye[$pool[$i]->id])) {
                        $byePlayer = $pool[$i];
                        array_splice($pool, $i, 1);
                        break;
                    }
                }
                if ($byePlayer === null) {
                    $byePlayer = array_pop($pool);
                }
            }

            // Find valid pairings avoiding rematches
            $pairings = $this->findPairings($pool, $playedMap);
            if ($pairings === null) {
                // Fallback: pair sequentially if impossible to satisfy strict rematch rule
                $pairings = [];
                for ($i = 0; $i < count($pool) - 1; $i += 2) {
                    $pairings[] = [$pool[$i], $pool[$i + 1]];
                }
            }

            $round = Round::create([
                'tournament_id' => $tournament->id,
                'round_number' => $roundNumber,
            ]);

            $timeControls = $tournament->timeControls;

            if ($byePlayer) {
                $this->matchGameFactory->createMatch($round, $byePlayer, null, true, $timeControls);
            }

            foreach ($pairings as [$p1, $p2]) {
                // Balance colors: player with fewer whites gets White
                $w1 = $whiteCount[$p1->id] ?? 0;
                $w2 = $whiteCount[$p2->id] ?? 0;

                if ($w1 > $w2) {
                    [$p1, $p2] = [$p2, $p1];
                }

                $this->matchGameFactory->createMatch($round, $p1, $p2, false, $timeControls);
            }

            return $round;
        });
    }

    /**
     * Backtracking algorithm to find non-repeating pairings.
     */
    protected function findPairings(array $players, array $playedMap): ?array
    {
        if (empty($players)) {
            return [];
        }

        $first = array_shift($players);

        for ($i = 0; $i < count($players); $i++) {
            $candidate = $players[$i];
            $key = min($first->id, $candidate->id) . '_' . max($first->id, $candidate->id);

            if (! isset($playedMap[$key])) {
                $remaining = $players;
                array_splice($remaining, $i, 1);

                $subResult = $this->findPairings($remaining, $playedMap);
                if ($subResult !== null) {
                    array_unshift($subResult, [$first, $candidate]);
                    return $subResult;
                }
            }
        }

        return null;
    }
}
