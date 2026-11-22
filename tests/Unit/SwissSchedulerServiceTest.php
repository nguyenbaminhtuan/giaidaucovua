<?php

namespace Tests\Unit;

use App\Models\Participant;
use App\Models\Tournament;
use App\Models\TournamentTimeControl;
use App\Services\Scheduling\MatchGameFactory;
use App\Services\Scheduling\SwissSchedulerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SwissSchedulerServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_swiss_system_pairs_consecutive_rounds_without_rematches(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Thụy Sĩ 6 Kỳ Thủ', 'format' => 'swiss']);

        $players = collect(['Kỳ thủ 1', 'Kỳ thủ 2', 'Kỳ thủ 3', 'Kỳ thủ 4', 'Kỳ thủ 5', 'Kỳ thủ 6'])->map(fn ($name) =>
            Participant::create(['tournament_id' => $tournament->id, 'name' => $name])
        );

        TournamentTimeControl::create([
            'tournament_id' => $tournament->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
        ]);

        $scheduler = new SwissSchedulerService(new MatchGameFactory);

        // Round 1
        $r1 = $scheduler->generateNextRound($tournament, 1);
        $this->assertNotNull($r1);
        $r1->load('matches');
        $this->assertCount(3, $r1->matches);

        $round1Pairs = [];
        foreach ($r1->matches as $m) {
            $key = min($m->player1_id, $m->player2_id) . '-' . max($m->player1_id, $m->player2_id);
            $round1Pairs[] = $key;
        }

        // Round 2
        $r2 = $scheduler->generateNextRound($tournament, 2);
        $this->assertNotNull($r2);
        $r2->load('matches');
        $this->assertCount(3, $r2->matches);

        $round2Pairs = [];
        foreach ($r2->matches as $m) {
            $key = min($m->player1_id, $m->player2_id) . '-' . max($m->player1_id, $m->player2_id);
            $round2Pairs[] = $key;
        }

        // Rematch check: No pair in Round 2 should exist in Round 1
        $intersection = array_intersect($round1Pairs, $round2Pairs);
        $this->assertEmpty($intersection, 'Swiss system must not pair the same opponents twice');
    }

    public function test_swiss_system_odd_player_count_rotates_byes(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Thụy Sĩ 5 Kỳ Thủ', 'format' => 'swiss']);

        $players = collect(['A', 'B', 'C', 'D', 'E'])->map(fn ($name) =>
            Participant::create(['tournament_id' => $tournament->id, 'name' => $name])
        );

        $scheduler = new SwissSchedulerService(new MatchGameFactory);

        // Round 1
        $r1 = $scheduler->generateNextRound($tournament, 1);
        $this->assertNotNull($r1);
        $r1->load('matches');
        $this->assertCount(3, $r1->matches); // 2 matches + 1 bye

        $r1ByeMatch = $r1->matches->firstWhere('is_bye', true);
        $this->assertNotNull($r1ByeMatch);
        $r1ByePlayerId = $r1ByeMatch->player1_id;

        // Round 2
        $r2 = $scheduler->generateNextRound($tournament, 2);
        $this->assertNotNull($r2);
        $r2->load('matches');

        $r2ByeMatch = $r2->matches->firstWhere('is_bye', true);
        $this->assertNotNull($r2ByeMatch);
        $r2ByePlayerId = $r2ByeMatch->player1_id;

        // The bye player in Round 2 should be different from Round 1
        $this->assertNotEquals($r1ByePlayerId, $r2ByePlayerId);
    }
}
