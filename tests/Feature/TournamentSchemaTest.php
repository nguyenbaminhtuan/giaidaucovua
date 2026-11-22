<?php

namespace Tests\Feature;

use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\TournamentTimeControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TournamentSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_tournament_models_and_relations_work_properly(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải Vô Địch Cờ Vua Mùa Xuân',
            'format' => 'round_robin_groups',
            'status' => 'in_progress',
        ]);

        $groupA = TournamentGroup::create([
            'tournament_id' => $tournament->id,
            'name' => 'Bảng A',
        ]);

        $p1 = Participant::create([
            'tournament_id' => $tournament->id,
            'group_id' => $groupA->id,
            'name' => 'Nguyễn Văn A',
        ]);

        $p2 = Participant::create([
            'tournament_id' => $tournament->id,
            'group_id' => $groupA->id,
            'name' => 'Trần Văn B',
        ]);

        $tc1 = TournamentTimeControl::create([
            'tournament_id' => $tournament->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
        ]);

        $tc2 = TournamentTimeControl::create([
            'tournament_id' => $tournament->id,
            'game_number' => 2,
            'base_minutes' => 15,
            'increment_seconds' => 2,
        ]);

        $round = Round::create([
            'tournament_id' => $tournament->id,
            'group_id' => $groupA->id,
            'round_number' => 1,
        ]);

        $match = ChessMatch::create([
            'round_id' => $round->id,
            'player1_id' => $p1->id,
            'player2_id' => $p2->id,
            'status' => 'pending',
        ]);

        $game1 = ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
            'white_player_id' => $p1->id,
            'black_player_id' => $p2->id,
            'result' => '1-0', // P1 wins as White
            'status' => 'completed',
        ]);

        $game2 = ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 2,
            'base_minutes' => 15,
            'increment_seconds' => 2,
            'white_player_id' => $p2->id, // Alternated colors
            'black_player_id' => $p1->id,
            'result' => '0.5-0.5', // Draw
            'status' => 'completed',
        ]);

        $match->refresh();
        $this->assertCount(2, $match->games);
        $this->assertTrue($match->areAllGamesFinished());

        $match->recalculateScores();
        $match->refresh();

        // P1 got 1.0 (game 1) + 0.5 (game 2) = 1.5
        // P2 got 0.0 (game 1) + 0.5 (game 2) = 0.5
        $this->assertEquals(1.5, $match->player1_score);
        $this->assertEquals(0.5, $match->player2_score);

        $standing = Standing::create([
            'tournament_id' => $tournament->id,
            'group_id' => $groupA->id,
            'participant_id' => $p1->id,
            'played' => 1,
            'won' => 1,
            'drawn' => 0,
            'lost' => 0,
            'points' => 1.5,
            'tie_break_score' => 0.0,
        ]);

        $this->assertEquals(1.5, $standing->points);
    }
}
