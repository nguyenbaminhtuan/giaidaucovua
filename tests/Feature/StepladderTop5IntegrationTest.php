<?php

namespace Tests\Feature;

use App\Livewire\Tournament\MatchRow;
use App\Livewire\Tournament\SetupWizard;
use App\Livewire\Tournament\TournamentDashboard;
use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StepladderTop5IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_wizard_creates_stepladder_top5_tournament(): void
    {
        session(['is_admin' => true]);

        Livewire::test(SetupWizard::class)
            ->set('name', 'Vòng Chung Kết 5 Người')
            ->set('format', 'stepladder_top5')
            ->set('stepladder_players', [
                0 => 'Nhất Bảng (Hạng 1)',
                1 => 'Nhì Bảng (Hạng 2)',
                2 => 'Ba Bảng (Hạng 3)',
                3 => 'Tư Bảng (Hạng 4)',
                4 => 'Năm Bảng (Hạng 5)',
            ])
            ->set('swiss_games', [
                ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
                ['game_number' => 2, 'base_minutes' => 5, 'increment_seconds' => 2],
                ['game_number' => 3, 'base_minutes' => 5, 'increment_seconds' => 2],
            ])
            ->call('confirmAndGenerate')
            ->assertHasNoErrors()
            ->assertRedirect();

        $t = Tournament::where('name', 'Vòng Chung Kết 5 Người')->first();
        $this->assertNotNull($t);
        $this->assertEquals('stepladder_top5', $t->format);
        $this->assertCount(5, $t->participants);

        // Check Round 1 (Trận 1: Hạng 2 vs 3, Trận 2: Hạng 4 vs 5)
        $r1 = $t->rounds()->first();
        $this->assertNotNull($r1);
        $this->assertCount(2, $r1->matches);
    }

    public function test_tied_match_allows_admin_to_select_winner_by_standings(): void
    {
        session(['is_admin' => true]);

        $tournament = Tournament::create(['name' => 'Giải Test Tie', 'format' => 'stepladder_top5']);
        $round = Round::create(['tournament_id' => $tournament->id, 'stage' => 'knockout', 'round_number' => 1]);

        $p2 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 2', 'seed' => 2]);
        $p3 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 3', 'seed' => 3]);

        $match = ChessMatch::create([
            'round_id' => $round->id,
            'player1_id' => $p2->id,
            'player2_id' => $p3->id,
            'player1_score' => 1.5,
            'player2_score' => 1.5,
            'status' => 'pending',
        ]);

        ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 1,
            'base_minutes' => 5,
            'increment_seconds' => 2,
            'white_player_id' => $p2->id,
            'black_player_id' => $p3->id,
            'result' => '0.5-0.5',
            'status' => 'completed',
        ]);

        // Attempting to confirm without picking winner fails
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('confirmMatch')
            ->assertSee('Trận đấu đang hòa điểm. Hãy chọn người thắng theo BXH hoặc đấu thêm ván phụ!');

        // Admin selects Hạng 2 as winner based on standings tie-break
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('selectWinner', $p2->id)
            ->call('confirmMatch');

        $match->refresh();
        $this->assertEquals('completed', $match->status);
        $this->assertEquals($p2->id, $match->winner_id);
        $this->assertEquals($p2->id, $match->getWinner()->id);
        $this->assertEquals($p3->id, $match->getLoser()->id);
    }

    public function test_dashboard_generates_stepladder_finals_after_round1_completed(): void
    {
        session(['is_admin' => true]);

        $tournament = Tournament::create(['name' => 'Giải Top 5 Playoff', 'format' => 'stepladder_top5']);
        $r1 = Round::create(['tournament_id' => $tournament->id, 'stage' => 'knockout', 'round_number' => 1]);

        $p1 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 1', 'seed' => 1]);
        $p2 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 2', 'seed' => 2]);
        $p3 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 3', 'seed' => 3]);
        $p4 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 4', 'seed' => 4]);
        $p5 = Participant::create(['tournament_id' => $tournament->id, 'name' => 'Hạng 5', 'seed' => 5]);

        // Match 1: Hạng 2 vs 3 (Hạng 2 wins)
        ChessMatch::create([
            'round_id' => $r1->id,
            'player1_id' => $p2->id,
            'player2_id' => $p3->id,
            'player1_score' => 2.0,
            'player2_score' => 1.0,
            'status' => 'completed',
        ]);

        // Match 2: Hạng 4 vs 5 (Hạng 5 wins)
        ChessMatch::create([
            'round_id' => $r1->id,
            'player1_id' => $p4->id,
            'player2_id' => $p5->id,
            'player1_score' => 1.0,
            'player2_score' => 2.0,
            'status' => 'completed',
        ]);

        Livewire::test(TournamentDashboard::class, ['tournament' => $tournament])
            ->call('generateStepladderFinals')
            ->assertSee('Đã khởi tạo xong Trận Tranh HCV và Tranh HCĐ!');

        $this->assertCount(2, $tournament->rounds);
        $r2 = $tournament->rounds()->where('round_number', 2)->first();
        $this->assertNotNull($r2);
        $this->assertCount(2, $r2->matches);

        // Gold Match: Winner T1 (Hạng 2) vs Hạng 1
        $goldMatch = $r2->matches[0];
        $this->assertEquals($p2->id, $goldMatch->player1_id);
        $this->assertEquals($p1->id, $goldMatch->player2_id);

        // Bronze Match: Loser T1 (Hạng 3) vs Winner T2 (Hạng 5)
        $bronzeMatch = $r2->matches[1];
        $this->assertEquals($p3->id, $bronzeMatch->player1_id);
        $this->assertEquals($p5->id, $bronzeMatch->player2_id);
    }
}
