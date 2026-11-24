<?php

namespace Tests\Feature;

use App\Livewire\Tournament\Stage2Board;
use App\Models\Participant;
use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class Stage2IntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_stage2_board_renders_and_initializes_7_rounds_and_promotes_to_stepladder(): void
    {
        session(['is_admin' => true]);

        $tournament = Tournament::create([
            'name' => 'Giải 2 Bảng Toàn Diện',
            'format' => 'round_robin_groups',
            'advancing_count' => 4,
            'bonus_top1' => 4.0,
            'bonus_top2' => 3.0,
            'bonus_top3' => 2.0,
        ]);

        $gA = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng A']);
        $gB = TournamentGroup::create(['tournament_id' => $tournament->id, 'name' => 'Bảng B']);

        for ($i = 1; $i <= 5; $i++) {
            $pA = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'name' => "Kỳ thủ A{$i}"]);
            $pB = Participant::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'name' => "Kỳ thủ B{$i}"]);

            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gA->id, 'participant_id' => $pA->id, 'points' => 10 - $i]);
            Standing::create(['tournament_id' => $tournament->id, 'group_id' => $gB->id, 'participant_id' => $pB->id, 'points' => 10 - $i]);
        }

        Livewire::test(Stage2Board::class, ['tournamentId' => $tournament->id])
            ->assertSee('Giai Đoạn 2')
            ->call('generateStage2Rounds')
            ->assertSee('Vòng 1')
            ->assertSee('Vòng 7')
            ->call('switchSubTab', 'combined_standings')
            ->assertSee('Bảng Xếp Hạng Tổng Hợp Giai Đoạn 2')
            ->assertSee('Điểm Vòng Tròn')
            ->assertSee('Điểm Thưởng')
            ->call('promoteToStepladder')
            ->assertDispatched('switch-to-stepladder');

        $this->assertDatabaseHas('rounds', [
            'tournament_id' => $tournament->id,
            'stage' => 'stage2_round_robin',
            'round_number' => 7,
        ]);

        $this->assertDatabaseHas('rounds', [
            'tournament_id' => $tournament->id,
            'stage_name' => 'Giai Đoạn 1 (Trận 1 & Trận 2)',
        ]);
    }
}
