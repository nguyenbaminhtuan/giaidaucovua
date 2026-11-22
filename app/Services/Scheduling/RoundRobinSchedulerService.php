<?php

namespace App\Services\Scheduling;

use App\Models\Participant;
use App\Models\Round;
use App\Models\TournamentGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RoundRobinSchedulerService
{
    public function __construct(
        protected MatchGameFactory $matchGameFactory
    ) {}

    /**
     * Generate all round-robin rounds and matches for a tournament group.
     *
     * @param  TournamentGroup  $group
     * @return Collection<int, Round>
     */
    public function generate(TournamentGroup $group): Collection
    {
        return DB::transaction(function () use ($group) {
            $group->loadMissing(['tournament.timeControls', 'timeControls', 'participants']);
            $participants = $group->participants->values()->all();
            $timeControls = $group->timeControls->isNotEmpty()
                ? $group->timeControls
                : $group->tournament->timeControls;

            // Remove existing rounds in this group if re-generating
            $group->rounds()->delete();

            $totalParticipants = count($participants);
            if ($totalParticipants < 2) {
                return collect();
            }

            // If odd, append null (dummy Bye player)
            $players = $participants;
            if ($totalParticipants % 2 !== 0) {
                $players[] = null;
            }

            $numPlayers = count($players);
            $totalRounds = $numPlayers - 1;
            $matchesPerRound = (int) ($numPlayers / 2);

            $createdRounds = collect();

            for ($r = 0; $r < $totalRounds; $r++) {
                $roundNumber = $r + 1;
                $round = Round::create([
                    'tournament_id' => $group->tournament_id,
                    'group_id' => $group->id,
                    'round_number' => $roundNumber,
                ]);

                for ($m = 0; $m < $matchesPerRound; $m++) {
                    $p1 = $players[$m];
                    $p2 = $players[$numPlayers - 1 - $m];

                    // Determine home/away alternation to balance colors
                    if ($m === 0) {
                        if ($r % 2 === 1) {
                            [$p1, $p2] = [$p2, $p1];
                        }
                    } else {
                        if (($m + $r) % 2 === 1) {
                            [$p1, $p2] = [$p2, $p1];
                        }
                    }

                    // Handle Bye when one of the pair is null
                    if ($p1 === null && $p2 !== null) {
                        $this->matchGameFactory->createMatch($round, $p2, null, true, $timeControls);
                    } elseif ($p2 === null && $p1 !== null) {
                        $this->matchGameFactory->createMatch($round, $p1, null, true, $timeControls);
                    } elseif ($p1 !== null && $p2 !== null) {
                        $this->matchGameFactory->createMatch($round, $p1, $p2, false, $timeControls);
                    }
                }

                // Cyclic rotation: keep index 0 fixed, rotate others
                $last = array_pop($players);
                array_splice($players, 1, 0, [$last]);

                $createdRounds->push($round);
            }

            return $createdRounds;
        });
    }
}
