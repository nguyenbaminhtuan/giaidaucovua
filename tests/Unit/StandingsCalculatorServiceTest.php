<?php

namespace Tests\Unit;

use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Standings\StandingsCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StandingsCalculatorServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_standings_calculation_awards_correct_points_and_sonneborn_berger(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Vòng Tròn 3 Kỳ Thủ', 'format' => 'round_robin_groups']);
        $group = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);

        $pA = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Kỳ thủ A']);
        $pB = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Kỳ thủ B']);
        $pC = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Kỳ thủ C']);

        $r1 = Round::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'round_number' => 1]);
        $r2 = Round::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'round_number' => 2]);
        $r3 = Round::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'round_number' => 3]);

        // Match 1: A vs B -> A wins (1.0 vs 0.0)
        ChessMatch::create([
            'round_id' => $r1->id,
            'player1_id' => $pA->id,
            'player2_id' => $pB->id,
            'player1_score' => 1.0,
            'player2_score' => 0.0,
            'status' => 'completed',
        ]);

        // Match 2: B vs C -> B wins (1.0 vs 0.0)
        ChessMatch::create([
            'round_id' => $r2->id,
            'player1_id' => $pB->id,
            'player2_id' => $pC->id,
            'player1_score' => 1.0,
            'player2_score' => 0.0,
            'status' => 'completed',
        ]);

        // Match 3: A vs C -> Draw (0.5 vs 0.5)
        ChessMatch::create([
            'round_id' => $r3->id,
            'player1_id' => $pA->id,
            'player2_id' => $pC->id,
            'player1_score' => 0.5,
            'player2_score' => 0.5,
            'status' => 'completed',
        ]);

        $calculator = new StandingsCalculatorService;
        $standings = $calculator->recalculateGroup($group);

        // A has 1 win (1.0) + 1 draw (0.5) = 1.5 pts
        // B has 1 win (1.0) + 1 loss (0.0) = 1.0 pts
        // C has 1 draw (0.5) + 1 loss (0.0) = 0.5 pts
        $this->assertEquals($pA->id, $standings[0]->participant_id);
        $this->assertEquals(1.5, $standings[0]->points);

        $this->assertEquals($pB->id, $standings[1]->participant_id);
        $this->assertEquals(1.0, $standings[1]->points);

        $this->assertEquals($pC->id, $standings[2]->participant_id);
        $this->assertEquals(0.5, $standings[2]->points);

        // A's Sonneborn-Berger: beat B (B has 1.0) -> +1.0, drew C (C has 0.5) -> +0.25 => SB = 1.25
        $this->assertEquals(1.25, $standings[0]->tie_break_score);
    }
}
