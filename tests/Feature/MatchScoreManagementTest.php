<?php

namespace Tests\Feature;

use App\Events\MatchResultConfirmed;
use App\Livewire\Tournament\MatchRow;
use App\Livewire\Tournament\SetupWizard;
use App\Models\ChessGame;
use App\Models\ChessMatch;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Models\TournamentTimeControl;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Tests\TestCase;

class MatchScoreManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_setup_wizard_creates_tournament_with_per_group_time_controls(): void
    {
        session(['is_admin' => true]);

        Livewire::test(SetupWizard::class)
            ->set('name', 'Giải Đấu Nhiều Bảng Khác Giờ')
            ->set('format', 'round_robin_groups')
            ->set('groups', [
                [
                    'name' => 'Bảng A',
                    'players_text' => "A1\nA2\nA3",
                    'games' => [
                        ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
                        ['game_number' => 2, 'base_minutes' => 15, 'increment_seconds' => 2],
                    ],
                ],
                [
                    'name' => 'Bảng B',
                    'players_text' => "B1\nB2\nB3",
                    'games' => [
                        ['game_number' => 1, 'base_minutes' => 10, 'increment_seconds' => 5],
                    ],
                ],
            ])
            ->call('confirmAndGenerate')
            ->assertHasNoErrors()
            ->assertRedirect();

        $tournament = Tournament::where('name', 'Giải Đấu Nhiều Bảng Khác Giờ')->first();
        $this->assertNotNull($tournament);

        $groupA = $tournament->groups()->where('name', 'Bảng A')->first();
        $groupB = $tournament->groups()->where('name', 'Bảng B')->first();

        $this->assertCount(2, $groupA->timeControls);
        $this->assertEquals(5, $groupA->timeControls[0]->base_minutes);
        $this->assertEquals(15, $groupA->timeControls[1]->base_minutes);

        $this->assertCount(1, $groupB->timeControls);
        $this->assertEquals(10, $groupB->timeControls[0]->base_minutes);

        // Check games generated inside matches for each group
        $roundA = $groupA->rounds()->first();
        $matchA = $roundA->matches()->where('is_bye', false)->first();
        $this->assertCount(2, $matchA->games);

        $roundB = $groupB->rounds()->first();
        $matchB = $roundB->matches()->where('is_bye', false)->first();
        $this->assertCount(1, $matchB->games);
        $this->assertEquals(10, $matchB->games[0]->base_minutes);
    }

    public function test_match_row_records_game_results_and_confirms_with_green_checkmark(): void
    {
        session(['is_admin' => true]);
        Event::fake([MatchResultConfirmed::class]);

        $tournament = Tournament::create(['name' => 'Giải Test', 'format' => 'round_robin_groups']);
        $group = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);

        $p1 = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Alice']);
        $p2 = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'name' => 'Bob']);

        $round = Round::create(['tournament_id' => $tournament->id, 'group_id' => $group->id, 'round_number' => 1]);

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
            'result' => null,
            'status' => 'pending',
        ]);

        $g2 = ChessGame::create([
            'match_id' => $match->id,
            'game_number' => 2,
            'base_minutes' => 15,
            'increment_seconds' => 2,
            'white_player_id' => $p2->id,
            'black_player_id' => $p1->id,
            'result' => null,
            'status' => 'pending',
        ]);

        // Test Livewire interaction
        Livewire::test(MatchRow::class, ['matchId' => $match->id])
            ->call('toggleOpen')
            ->assertSet('isOpen', true)
            // Game 1: 1-0 (White/P1 wins)
            ->call('setGameResult', $g1->id, '1-0')
            // Game 2: 0.5-0.5 (Draw)
            ->call('setGameResult', $g2->id, '0.5-0.5')
            // Confirm match with green checkmark
            ->call('confirmMatch')
            ->assertDispatched('match-updated');

        $match->refresh();
        $this->assertEquals('completed', $match->status);
        $this->assertEquals(1.5, $match->player1_score);
        $this->assertEquals(0.5, $match->player2_score);

        Event::assertDispatched(MatchResultConfirmed::class, function ($event) use ($match) {
            return $event->match->id === $match->id;
        });
    }
}
