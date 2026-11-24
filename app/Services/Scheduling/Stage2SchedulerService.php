<?php

namespace App\Services\Scheduling;

use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Stage2SchedulerService
{
    public function __construct(
        protected MatchGameFactory $matchGameFactory,
        protected StepladderSchedulerService $stepladderScheduler
    ) {}

    public function generateStage2RoundRobin(Tournament $tournament, $timeControls = []): Collection
    {
        return DB::transaction(function () use ($tournament, $timeControls) {
            $tournament->loadMissing(['groups', 'standings.participant']);

            $advancingCount = $tournament->advancing_count ?: 4;
            $groups = $tournament->groups->values();

            $topA = $this->getTopParticipants($tournament->id, $groups[0]?->id, $advancingCount);
            $topB = $this->getTopParticipants($tournament->id, $groups[1]?->id, $advancingCount);

            $players = array_merge($topA, $topB);
            if (count($players) < 8) {
                return collect();
            }

            $tournament->rounds()->where('stage', 'stage2_round_robin')->delete();

            $numPlayers = 8;
            $totalRounds = 7;
            $matchesPerRound = 4;
            $createdRounds = collect();
            $rotating = $players;

            for ($r = 0; $r < $totalRounds; $r++) {
                $roundNumber = $r + 1;
                $round = Round::create([
                    'tournament_id' => $tournament->id,
                    'stage' => 'stage2_round_robin',
                    'stage_name' => "Vòng Phân Hạng - Vòng {$roundNumber}",
                    'round_number' => $roundNumber,
                ]);

                for ($m = 0; $m < $matchesPerRound; $m++) {
                    $p1 = $rotating[$m];
                    $p2 = $rotating[$numPlayers - 1 - $m];

                    if (($m === 0 && $r % 2 === 1) || ($m > 0 && ($m + $r) % 2 === 1)) {
                        [$p1, $p2] = [$p2, $p1];
                    }

                    $this->matchGameFactory->createMatch($round, $p1, $p2, false, $timeControls);
                }

                $last = array_pop($rotating);
                array_splice($rotating, 1, 0, [$last]);
                $createdRounds->push($round);
            }

            foreach ($players as $p) {
                Standing::firstOrCreate(
                    [
                        'tournament_id' => $tournament->id,
                        'stage' => 'stage2_round_robin',
                        'participant_id' => $p->id,
                    ],
                    [
                        'group_id' => null, 'played' => 0, 'won' => 0, 'drawn' => 0,
                        'lost' => 0, 'points' => 0.0, 'bonus_points' => 0.0, 'tie_break_score' => 0.0,
                    ]
                );
            }

            $this->syncBonusPoints($tournament);

            return $createdRounds;
        });
    }

    public function syncBonusPoints(Tournament $tournament): void
    {
        $finalsRound = $tournament->rounds()
            ->where('stage', 'knockout')
            ->where('stage_name', 'like', '%Chung Kết%')
            ->first();

        if (! $finalsRound) {
            return;
        }

        $matches = $finalsRound->matches()->orderBy('id')->get();
        if ($matches->count() < 2) {
            return;
        }

        $rewards = [
            $matches[0]->getWinner()?->id => (float) ($tournament->bonus_top1 ?: 4.0),
            $matches[0]->getLoser()?->id => (float) ($tournament->bonus_top2 ?: 3.0),
            $matches[1]->getWinner()?->id => (float) ($tournament->bonus_top3 ?: 2.0),
        ];

        foreach ($rewards as $pid => $bonus) {
            if ($pid) {
                Standing::where('tournament_id', $tournament->id)
                    ->where('stage', 'stage2_round_robin')
                    ->where('participant_id', $pid)
                    ->update(['bonus_points' => $bonus]);
            }
        }
    }

    public function promoteToStepladder(Tournament $tournament, $timeControls = []): ?Round
    {
        return DB::transaction(function () use ($tournament, $timeControls) {
            $this->syncBonusPoints($tournament);

            $top5Standings = Standing::with('participant')
                ->where('tournament_id', $tournament->id)
                ->where('stage', 'stage2_round_robin')
                ->get()
                ->sortByDesc(fn ($st) => $st->total_points * 1000 + $st->tie_break_score)
                ->take(5)
                ->values();

            if ($top5Standings->count() < 5) {
                return null;
            }

            foreach ($top5Standings as $idx => $st) {
                $p = $st->participant;
                $p->seed = $idx + 1;
                $p->save();
            }

            $tournament->rounds()->where('stage', 'stepladder')->delete();

            return $this->stepladderScheduler->generateRound1($tournament, $timeControls);
        });
    }

    protected function getTopParticipants(int $tournamentId, ?int $groupId, int $limit): array
    {
        if (! $groupId) {
            return [];
        }

        $standings = Standing::with('participant')
            ->where('tournament_id', $tournamentId)
            ->where('group_id', $groupId)
            ->orderByDesc('points')
            ->orderByDesc('tie_break_score')
            ->orderByDesc('won')
            ->limit($limit)
            ->get();

        if ($standings->count() >= $limit) {
            return $standings->pluck('participant')->all();
        }

        return Participant::where('tournament_id', $tournamentId)
            ->where('group_id', $groupId)
            ->limit($limit)
            ->get()
            ->all();
    }
}
