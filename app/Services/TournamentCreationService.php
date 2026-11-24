<?php

namespace App\Services;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\TournamentTimeControl;
use App\Services\Scheduling\RoundRobinSchedulerService;
use App\Services\Scheduling\StepladderSchedulerService;
use App\Services\Scheduling\SwissSchedulerService;
use Illuminate\Support\Facades\DB;

class TournamentCreationService
{
    public function __construct(
        protected RoundRobinSchedulerService $rrScheduler,
        protected SwissSchedulerService $swissScheduler,
        protected StepladderSchedulerService $stepladderScheduler
    ) {}

    public function create(
        string $name,
        string $format,
        array $groups,
        array $swissGames,
        string $swissPlayersText,
        int $advancingCount = 4,
        array $stepladderPlayers = []
    ): Tournament {
        return DB::transaction(function () use ($name, $format, $groups, $swissGames, $swissPlayersText, $advancingCount, $stepladderPlayers) {
            $tournament = Tournament::create([
                'name' => $name,
                'format' => $format,
                'advancing_count' => $advancingCount,
                'status' => 'in_progress',
            ]);

            if ($format === 'round_robin_groups') {
                $this->createRoundRobinGroups($tournament, $groups);
            } elseif ($format === 'stepladder_top5') {
                $this->createStepladderTournament($tournament, $stepladderPlayers, $swissGames);
            } else {
                $this->createSwissTournament($tournament, $swissGames, $swissPlayersText);
            }

            return $tournament;
        });
    }

    protected function createRoundRobinGroups(Tournament $tournament, array $groups): void
    {
        foreach ($groups as $groupData) {
            $group = TournamentGroup::create([
                'tournament_id' => $tournament->id,
                'name' => $groupData['name'],
            ]);

            foreach ($groupData['games'] as $g) {
                TournamentTimeControl::create([
                    'tournament_id' => $tournament->id,
                    'group_id' => $group->id,
                    'game_number' => (int) $g['game_number'],
                    'base_minutes' => (int) $g['base_minutes'],
                    'increment_seconds' => (int) $g['increment_seconds'],
                ]);
            }

            $playerNames = preg_split('/\r\n|\r|\n/', (string) $groupData['players_text']);
            foreach ($playerNames as $pName) {
                $pName = trim((string) $pName);
                if (! empty($pName)) {
                    Participant::create([
                        'tournament_id' => $tournament->id,
                        'group_id' => $group->id,
                        'name' => $pName,
                    ]);
                }
            }

            $this->rrScheduler->generate($group);
        }
    }

    protected function createSwissTournament(Tournament $tournament, array $swissGames, string $swissPlayersText): void
    {
        foreach ($swissGames as $g) {
            TournamentTimeControl::create([
                'tournament_id' => $tournament->id,
                'group_id' => null,
                'game_number' => (int) $g['game_number'],
                'base_minutes' => (int) $g['base_minutes'],
                'increment_seconds' => (int) $g['increment_seconds'],
            ]);
        }

        $playerNames = preg_split('/\r\n|\r|\n/', (string) $swissPlayersText);
        foreach ($playerNames as $pName) {
            $pName = trim((string) $pName);
            if (! empty($pName)) {
                Participant::create([
                    'tournament_id' => $tournament->id,
                    'name' => $pName,
                ]);
            }
        }

        $this->swissScheduler->generateNextRound($tournament, 1);
    }

    protected function createStepladderTournament(Tournament $tournament, array $players, array $games): void
    {
        foreach ($games as $g) {
            TournamentTimeControl::create([
                'tournament_id' => $tournament->id,
                'group_id' => null,
                'game_number' => (int) $g['game_number'],
                'base_minutes' => (int) $g['base_minutes'],
                'increment_seconds' => (int) $g['increment_seconds'],
            ]);
        }

        foreach ($players as $idx => $pName) {
            $pName = trim((string) $pName);
            if (! empty($pName)) {
                Participant::create([
                    'tournament_id' => $tournament->id,
                    'name' => $pName,
                    'seed' => $idx + 1,
                ]);
            }
        }

        $this->stepladderScheduler->generateRound1($tournament, $games);
    }
}
