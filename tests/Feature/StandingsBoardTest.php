<?php

namespace Tests\Feature;

use App\Livewire\Tournament\StandingsBoard;
use App\Models\Participant;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class StandingsBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_standings_board_renders_ranked_players_correctly(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Test BXH', 'format' => 'round_robin_groups']);
        $group = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);

        $p1 = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Nhất Bảng']);
        $p2 = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Nhì Bảng']);

        Standing::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'participant_id' => $p1->id,
            'played' => 1,
            'won' => 1,
            'drawn' => 0,
            'lost' => 0,
            'points' => 1.0,
            'tie_break_score' => 0.0,
        ]);

        Standing::create([
            'tournament_id' => $tournament->id,
            'group_id' => $group->id,
            'participant_id' => $p2->id,
            'played' => 1,
            'won' => 0,
            'drawn' => 0,
            'lost' => 1,
            'points' => 0.0,
            'tie_break_score' => 0.0,
        ]);

        Livewire::test(StandingsBoard::class, [
            'tournamentId' => $tournament->id,
            'selectedGroupId' => $group->id,
        ])
            ->assertSee('Bảng Xếp Hạng Bảng A')
            ->assertSee('Nhất Bảng')
            ->assertSee('Nhì Bảng');
    }
}
