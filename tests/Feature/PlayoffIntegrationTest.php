<?php

namespace Tests\Feature;

use App\Livewire\Tournament\MatchRow;
use App\Livewire\Tournament\PlayoffBoard;
use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PlayoffIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_playoff_board_renders_and_initiates_knockout_stage(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải 20 Kỳ Thủ Playoff',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
        ]);

        $gA = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);
        $gB = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng B']);

        for ($i = 1; $i <= 5; $i++) {
            $pA = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'name' => "A{$i}"]);
            $pB = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'name' => "B{$i}"]);

            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'participant_id' => $pA->id, 'points' => 10 - $i]);
            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'participant_id' => $pB->id, 'points' => 10 - $i]);
        }

        Livewire::test(PlayoffBoard::class, ['tournamentId' => $tournament->id])
            ->assertSee('Knockout Stage')
            ->assertSee('Khởi Tạo Vòng Loại Trực Tiếp')
            ->call('updateAdvancingCount', 4)
            ->call('startKnockoutStage')
            ->assertSee('Tứ Kết');

        $this->assertDatabaseHas('rounds', [
            'tournament_id' => $tournament->id,
            'stage' => 'knockout',
            'stage_name' => 'Tứ Kết',
        ]);

        $this->assertEquals(4, ChessMatch::whereHas('round', fn ($q) => $q->where('stage', 'knockout'))->count());
    }

    public function test_knockout_match_requires_tie_break_if_tied_score(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Playoff Test', 'format' => 'round_robin_groups']);
        $round = Round::create([
            'tournament_id' => $tournament->id,
            'stage' => 'knockout',
            'stage_name' => 'Tứ Kết',
            'round_number' => 1,
        ]);

        $p1 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Alice']);
        $p2 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Bob']);

        $match = ChessMatch::create([
            'round_id' => $round->id,
            'player1_id' => $p1->id,
            'player2_id' => $p2->id,
            'status' => 'pending',
        ]);

        $g1 = ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
            'white_player_id' => $p1->id,
            'black_player_id' => $p2->id,
            'result' => '1-0', // Alice wins game 1 (1.0 - 0.0)
            'status' => 'completed',
        ]);

        $g2 = ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 2,
            'base_minutes' => 15,
            'increment_seconds' => 2,
            'white_player_id' => $p2->id,
            'black_player_id' => $p1->id,
            'result' => '1-0', // Bob wins game 2 as White (Bob +1.0) => Tie 1.0 - 1.0!
            'status' => 'completed',
        ]);

        $match->recalculateScores();
        $this->assertTrue($match->isTied());

        // Attempting to confirm tied knockout match fails
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('confirmMatch')
            ->assertSee('Trận đấu loại trực tiếp đang hòa. Cần đấu thêm ván phụ Tie-break!');

        $match->refresh();
        $this->assertEquals('pending', $match->status);

        // Add Tie-break game
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('addTieBreakGame');

        $this->assertCount(3, $match->fresh()->games);
        $g3 = $match->fresh()->games->last();
        $this->assertTrue($g3->is_tie_break);

        // Alice (White) wins the Tie-break (1-0)
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('setGameResult', $g3->id, '1-0')
            ->call('confirmMatch');

        $match->refresh();
        $this->assertEquals('completed', $match->status);
        $this->assertEquals(2.0, $match->player1_score);
        $this->assertEquals(1.0, $match->player2_score);
        $this->assertEquals($p1->id, $match->getWinner()->id);
    }

    public function test_playoff_header_and_description_can_be_edited(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải Header Edit',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
        ]);

        Livewire::test(PlayoffBoard::class, ['tournamentId' => $tournament->id])
            ->assertSee('Vòng Loại Trực Tiếp (Playoff)')
            ->call('startEditingHeader')
            ->assertSet('isEditingHeader', true)
            ->set('editingTitle', 'Vòng Chung Kết Tranh Ngai Vàng')
            ->set('editingDescription', '8 kỳ thủ xuất sắc nhất tranh đấu để tìm ra nhà Vô địch 2026.')
            ->call('saveHeader')
            ->assertSet('isEditingHeader', false)
            ->assertSee('Vòng Chung Kết Tranh Ngai Vàng')
            ->assertSee('8 kỳ thủ xuất sắc nhất tranh đấu');

        $tournament->refresh();
        $this->assertEquals('Vòng Chung Kết Tranh Ngai Vàng', $tournament->playoff_title);
        $this->assertEquals('8 kỳ thủ xuất sắc nhất tranh đấu để tìm ra nhà Vô địch 2026.', $tournament->playoff_description);
    }
}
