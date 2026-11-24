<?php

namespace App\Services\Standings;

use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Scheduling\Stage2SchedulerService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StandingsCalculatorService
{
    public function recalculateForMatch(ChessMatch $match): void
    {
        $match->loadMissing(['round.group', 'round.tournament']);
        $tournament = $match->round->tournament;
        $group = $match->round->group;
        $stage = $match->round->stage;

        if ($stage === 'stage2_round_robin') {
            $this->recalculateStage2RoundRobin($tournament);
        } elseif ($stage === 'knockout') {
            app(Stage2SchedulerService::class)->syncBonusPoints($tournament);
        } elseif ($tournament->format === 'round_robin_groups' && $group) {
            $this->recalculateGroup($group);
        } else {
            $this->recalculateTournament($tournament);
        }
    }

    public function recalculateGroup(TournamentGroup $group): Collection
    {
        return DB::transaction(function () use ($group) {
            $group->loadMissing(['participants', 'rounds.matches']);
            $participants = $group->participants;
            $completedMatches = $group->rounds->flatMap->matches->where('status', 'completed');

            return $this->computeAndSaveStandings(
                $group->tournament_id,
                $group->id,
                $participants,
                $completedMatches,
                'sonneborn_berger',
                'group_stage'
            );
        });
    }

    public function recalculateStage2RoundRobin(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament) {
            $rounds = $tournament->rounds()->where('stage', 'stage2_round_robin')->with('matches')->get();
            $completedMatches = $rounds->flatMap->matches->where('status', 'completed');

            $participantIds = $rounds->flatMap->matches->pluck('player1_id')
                ->merge($rounds->flatMap->matches->pluck('player2_id'))
                ->filter()->unique();

            $participants = Participant::whereIn('id', $participantIds)->get();

            $standings = $this->computeAndSaveStandings(
                $tournament->id,
                null,
                $participants,
                $completedMatches,
                'sonneborn_berger',
                'stage2_round_robin'
            );

            app(Stage2SchedulerService::class)->syncBonusPoints($tournament);

            return $standings;
        });
    }

    public function recalculateTournament(Tournament $tournament): Collection
    {
        return DB::transaction(function () use ($tournament) {
            $tournament->loadMissing(['participants', 'rounds.matches']);
            $participants = $tournament->participants;
            $completedMatches = $tournament->rounds->flatMap->matches->where('status', 'completed');

            return $this->computeAndSaveStandings(
                $tournament->id,
                null,
                $participants,
                $completedMatches,
                'buchholz',
                'group_stage'
            );
        });
    }

    protected function computeAndSaveStandings(
        int $tournamentId,
        ?int $groupId,
        Collection $participants,
        Collection $completedMatches,
        string $tieBreakMethod,
        string $stage = 'group_stage'
    ): Collection {
        $stats = [];
        foreach ($participants as $p) {
            $stats[$p->id] = [
                'played' => 0, 'won' => 0, 'drawn' => 0, 'lost' => 0, 'points' => 0.0, 'opponents' => [],
            ];
        }

        foreach ($completedMatches as $m) {
            $p1Id = $m->player1_id;
            $p2Id = $m->player2_id;

            if ($m->is_bye) {
                if (isset($stats[$p1Id])) {
                    $stats[$p1Id]['played']++;
                    $stats[$p1Id]['won']++;
                    $stats[$p1Id]['points'] += (float) $m->player1_score;
                }
                continue;
            }

            if (! isset($stats[$p1Id]) || ! isset($stats[$p2Id])) {
                continue;
            }

            $stats[$p1Id]['played']++;
            $stats[$p2Id]['played']++;
            $s1 = (float) $m->player1_score;
            $s2 = (float) $m->player2_score;
            $stats[$p1Id]['points'] += $s1;
            $stats[$p2Id]['points'] += $s2;

            if ($s1 > $s2) {
                $stats[$p1Id]['won']++; $stats[$p2Id]['lost']++;
                $stats[$p1Id]['opponents'][] = ['id' => $p2Id, 'outcome' => 'win'];
                $stats[$p2Id]['opponents'][] = ['id' => $p1Id, 'outcome' => 'loss'];
            } elseif ($s1 < $s2) {
                $stats[$p1Id]['lost']++; $stats[$p2Id]['won']++;
                $stats[$p1Id]['opponents'][] = ['id' => $p2Id, 'outcome' => 'loss'];
                $stats[$p2Id]['opponents'][] = ['id' => $p1Id, 'outcome' => 'win'];
            } else {
                $stats[$p1Id]['drawn']++; $stats[$p2Id]['drawn']++;
                $stats[$p1Id]['opponents'][] = ['id' => $p2Id, 'outcome' => 'draw'];
                $stats[$p2Id]['opponents'][] = ['id' => $p1Id, 'outcome' => 'draw'];
            }
        }

        $standingsList = collect();
        foreach ($participants as $p) {
            $data = $stats[$p->id];
            $tbScore = 0.0;

            foreach ($data['opponents'] as $opp) {
                $oppPoints = $stats[$opp['id']]['points'] ?? 0.0;
                if ($tieBreakMethod === 'sonneborn_berger') {
                    if ($opp['outcome'] === 'win') {
                        $tbScore += $oppPoints;
                    } elseif ($opp['outcome'] === 'draw') {
                        $tbScore += 0.5 * $oppPoints;
                    }
                } elseif ($tieBreakMethod === 'buchholz') {
                    $tbScore += $oppPoints;
                }
            }

            $existingBonus = Standing::where('tournament_id', $tournamentId)
                ->where('stage', $stage)
                ->where('participant_id', $p->id)
                ->value('bonus_points') ?? 0.0;

            $standing = Standing::updateOrCreate(
                [
                    'tournament_id' => $tournamentId,
                    'group_id' => $groupId,
                    'stage' => $stage,
                    'participant_id' => $p->id,
                ],
                [
                    'played' => $data['played'],
                    'won' => $data['won'],
                    'drawn' => $data['drawn'],
                    'lost' => $data['lost'],
                    'points' => $data['points'],
                    'bonus_points' => $existingBonus,
                    'tie_break_score' => $tbScore,
                ]
            );

            $standingsList->push($standing);
        }

        return $standingsList->sortByDesc(fn ($st) => $st->total_points * 1000 + $st->tie_break_score)->values();
    }
}
