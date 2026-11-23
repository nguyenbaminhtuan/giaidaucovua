<?php

namespace App\Services\Scheduling;

use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class KnockoutSchedulerService
{
    public function __construct(
        protected MatchGameFactory $matchGameFactory
    ) {}

    public function generateQuarterFinals(Tournament $tournament, $timeControls = []): Round
    {
        return DB::transaction(function () use ($tournament, $timeControls) {
            $tournament->loadMissing(['groups', 'standings.participant']);

            $advancingCount = $tournament->advancing_count ?: 4;
            $groups = $tournament->groups->values();

            $topA = $this->getTopParticipantsForGroup($tournament->id, $groups[0]?->id, $advancingCount);
            $topB = $this->getTopParticipantsForGroup($tournament->id, $groups[1]?->id, $advancingCount);

            $pairs = [];
            if ($advancingCount >= 4 && count($topA) >= 4 && count($topB) >= 4) {
                $stageName = 'Tứ Kết';
                // QF1: 1A vs 4B
                $pairs[] = [$topA[0], $topB[3]];
                // QF2: 2B vs 3A
                $pairs[] = [$topB[1], $topA[2]];
                // QF3: 1B vs 4A
                $pairs[] = [$topB[0], $topA[3]];
                // QF4: 2A vs 3B
                $pairs[] = [$topA[1], $topB[2]];
            } elseif ($advancingCount >= 2 && count($topA) >= 2 && count($topB) >= 2) {
                $stageName = 'Bán Kết';
                $pairs[] = [$topA[0], $topB[1]];
                $pairs[] = [$topB[0], $topA[1]];
            } else {
                $stageName = 'Chung Kết';
                $pairs[] = [$topA[0] ?? null, $topB[0] ?? null];
            }

            $maxRound = $tournament->rounds()->max('round_number') ?? 0;
            $round = Round::create([
                'tournament_id' => $tournament->id,
                'stage' => 'knockout',
                'stage_name' => $stageName,
                'round_number' => $maxRound + 1,
            ]);

            foreach ($pairs as [$p1, $p2]) {
                if ($p1 && $p2) {
                    $this->matchGameFactory->createMatch($round, $p1, $p2, false, $timeControls);
                }
            }

            return $round;
        });
    }

    public function generateSemiFinals(Tournament $tournament, Round $qfRound, $timeControls = []): ?Round
    {
        return DB::transaction(function () use ($tournament, $qfRound, $timeControls) {
            $matches = $qfRound->matches()->orderBy('id')->get();
            if ($matches->count() < 4) {
                return null;
            }

            $w1 = $matches[0]->getWinner();
            $w2 = $matches[1]->getWinner();
            $w3 = $matches[2]->getWinner();
            $w4 = $matches[3]->getWinner();

            if (! $w1 || ! $w2 || ! $w3 || ! $w4) {
                return null;
            }

            $maxRound = $tournament->rounds()->max('round_number') ?? 0;
            $sfRound = Round::create([
                'tournament_id' => $tournament->id,
                'stage' => 'knockout',
                'stage_name' => 'Bán Kết',
                'round_number' => $maxRound + 1,
            ]);

            // SF1: Winner QF1 vs Winner QF2
            $this->matchGameFactory->createMatch($sfRound, $w1, $w2, false, $timeControls);
            // SF2: Winner QF3 vs Winner QF4
            $this->matchGameFactory->createMatch($sfRound, $w3, $w4, false, $timeControls);

            return $sfRound;
        });
    }

    public function generateFinals(Tournament $tournament, Round $sfRound, $timeControls = []): ?Round
    {
        return DB::transaction(function () use ($tournament, $sfRound, $timeControls) {
            $matches = $sfRound->matches()->orderBy('id')->get();
            if ($matches->count() < 2) {
                return null;
            }

            $w1 = $matches[0]->getWinner();
            $l1 = $matches[0]->getLoser();
            $w2 = $matches[1]->getWinner();
            $l2 = $matches[1]->getLoser();

            if (! $w1 || ! $w2 || ! $l1 || ! $l2) {
                return null;
            }

            $maxRound = $tournament->rounds()->max('round_number') ?? 0;
            $finalRound = Round::create([
                'tournament_id' => $tournament->id,
                'stage' => 'knockout',
                'stage_name' => 'Chung Kết & Tranh Hạng 3',
                'round_number' => $maxRound + 1,
            ]);

            // Match 1: Chung kết tranh Nhất - Nhì
            $this->matchGameFactory->createMatch($finalRound, $w1, $w2, false, $timeControls);

            // Match 2: Tranh Hạng 3
            $this->matchGameFactory->createMatch($finalRound, $l1, $l2, false, $timeControls);

            return $finalRound;
        });
    }

    protected function getTopParticipantsForGroup(int $tournamentId, ?int $groupId, int $limit): array
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

        // Fallback: if matches not yet played, fetch participants from group
        return Participant::where('tournament_id', $tournamentId)
            ->where('group_id', $groupId)
            ->limit($limit)
            ->get()
            ->all();
    }
}
