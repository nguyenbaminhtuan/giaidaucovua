<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\TournamentTimeControl;
use App\Services\Scheduling\MatchGameFactory;
use App\Services\Scheduling\RoundRobinSchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoundRobinSchedulerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_even_player_count_round_robin_schedule(): void
    {
        $tournament = Tournament::create(['name' => 'Giải 4 người', 'format' => 'round_robin_groups']);
        $group = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);

        $players = collect(['P1', 'P2', 'P3', 'P4'])->map(fn ($name) =>
            Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => $name])
        );

        TournamentTimeControl::create([
            'tournament_id' => $tournament->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
        ]);
        TournamentTimeControl::create([
            'tournament_id' => $tournament->id,
            'game_number' => 2,
            'base_minutes' => 15,
            'increment_seconds' => 2,
        ]);

        $scheduler = new RoundRobinSchedulerService(new MatchGameFactory);
        $rounds = $scheduler->generate($group);

        // 4 players -> 3 rounds
        $this->assertCount(3, $rounds);

        $matchPairs = [];
        foreach ($rounds as $round) {
            $round->load('matches.games');
            $this->assertCount(2, $round->matches); // 2 matches per round

            foreach ($round->matches as $match) {
                $this->assertFalse($match->is_bye);
                $this->assertCount(2, $match->games);

                // Verify game color alternation
                $g1 = $match->games[0];
                $g2 = $match->games[1];
                $this->assertEquals($match->player1_id, $g1->white_player_id);
                $this->assertEquals($match->player2_id, $g2->white_player_id);

                $key = min($match->player1_id, $match->player2_id) . '-' . max($match->player1_id, $match->player2_id);
                $matchPairs[] = $key;
            }
        }

        // 4 players -> 4*3/2 = 6 unique matches
        $this->assertCount(6, $matchPairs);
        $this->assertCount(6, array_unique($matchPairs));
    }

    public function test_odd_player_count_round_robin_schedule_handles_byes(): void
    {
        $tournament = Tournament::create(['name' => 'Giải 5 người', 'format' => 'round_robin_groups']);
        $group = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng B']);

        $players = collect(['A', 'B', 'C', 'D', 'E'])->map(fn ($name) =>
            Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => $name])
        );

        $scheduler = new RoundRobinSchedulerService(new MatchGameFactory);
        $rounds = $scheduler->generate($group);

        // 5 players -> 5 rounds
        $this->assertCount(5, $rounds);

        $byesPerPlayer = [];
        $normalMatchesPerPlayer = [];

        foreach ($players as $p) {
            $byesPerPlayer[$p->id] = 0;
            $normalMatchesPerPlayer[$p->id] = 0;
        }

        foreach ($rounds as $round) {
            $round->load('matches');
            // Each round has 3 matches: 2 normal + 1 bye
            $this->assertCount(3, $round->matches);

            foreach ($round->matches as $m) {
                if ($m->is_bye) {
                    $byesPerPlayer[$m->player1_id]++;
                    $this->assertEquals(1.0, $m->player1_score);
                    $this->assertEquals('completed', $m->status);
                } else {
                    $normalMatchesPerPlayer[$m->player1_id]++;
                    $normalMatchesPerPlayer[$m->player2_id]++;
                }
            }
        }

        // Every player has exactly 1 bye and exactly 4 normal matches
        foreach ($players as $p) {
            $this->assertEquals(1, $byesPerPlayer[$p->id]);
            $this->assertEquals(4, $normalMatchesPerPlayer[$p->id]);
        }
    }
}
