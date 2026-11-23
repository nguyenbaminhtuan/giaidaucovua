<?php

namespace Tests\Feature;

use App\Livewire\Tournament\TournamentDashboard;
use App\Models\Tournament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TournamentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_tournament_name_can_be_edited_from_dashboard(): void
    {
        session(['is_admin' => true]);

        $tournament = Tournament::create([
            'name' => 'Giải Cờ Vua Mùa Đông 2026',
            'format' => 'swiss',
            'status' => 'in_progress',
        ]);

        Livewire::test(TournamentDashboard::class, ['tournament' => $tournament])
            ->assertSee('Giải Cờ Vua Mùa Đông 2026')
            ->call('startEditingName')
            ->assertSet('isEditingName', true)
            ->set('editingName', 'Giải Vô Địch Toàn Quốc 2026')
            ->call('saveName')
            ->assertSet('isEditingName', false)
            ->assertSee('Giải Vô Địch Toàn Quốc 2026');

        $tournament->refresh();
        $this->assertEquals('Giải Vô Địch Toàn Quốc 2026', $tournament->name);
    }

    public function test_quick_search_matches_by_player_name(): void
    {
        $tournament = Tournament::create([
            'name' => 'Giải Siêu Đại Kiện Tướng',
            'format' => 'swiss',
            'status' => 'in_progress',
        ]);

        $p1 = \App\Models\Participant::create(['tournament_id' => $tournament->id, 'name' => 'Lê Quang Liêm']);
        $p2 = \App\Models\Participant::create(['tournament_id' => $tournament->id, 'name' => 'Nguyễn Ngọc Trường Sơn']);
        $p3 = \App\Models\Participant::create(['tournament_id' => $tournament->id, 'name' => 'Trần Tuấn Minh']);
        $p4 = \App\Models\Participant::create(['tournament_id' => $tournament->id, 'name' => 'Nguyễn Anh Khôi']);

        $r1 = \App\Models\Round::create(['tournament_id' => $tournament->id, 'round_number' => 1]);
        $r2 = \App\Models\Round::create(['tournament_id' => $tournament->id, 'round_number' => 2]);

        \App\Models\ChessMatch::create([
            'round_id' => $r1->id,
            'player1_id' => $p1->id,
            'player2_id' => $p2->id,
            'status' => 'pending',
        ]);

        \App\Models\ChessMatch::create([
            'round_id' => $r2->id,
            'player1_id' => $p1->id,
            'player2_id' => $p3->id,
            'status' => 'pending',
        ]);

        \App\Models\ChessMatch::create([
            'round_id' => $r2->id,
            'player1_id' => $p2->id,
            'player2_id' => $p4->id,
            'status' => 'pending',
        ]);

        $component = Livewire::test(TournamentDashboard::class, ['tournament' => $tournament])
            ->set('matchSearch', 'Quang Liêm');

        $matchedMatches = $component->viewData('matches');
        $this->assertCount(2, $matchedMatches);
        $this->assertTrue($component->viewData('isSearching'));

        // Check that player 1 is in all returned matches
        foreach ($matchedMatches as $m) {
            $this->assertTrue($m->player1_id === $p1->id || $m->player2_id === $p1->id);
        }

        $component->call('clearMatchSearch');
        $this->assertEquals('', $component->get('matchSearch'));
        $this->assertFalse($component->viewData('isSearching'));
    }
}
