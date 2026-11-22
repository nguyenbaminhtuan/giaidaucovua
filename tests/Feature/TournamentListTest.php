<?php

namespace Tests\Feature;

use App\Livewire\Tournament\TournamentList;
use App\Models\Participant;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TournamentListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tournament_list_page_renders_tournaments(): void
    {
        $t1 = Tournament::create(['name' => 'Giải Vô Địch Mùa Xuân', 'format' => 'round_robin_groups']);
        $t2 = Tournament::create(['name' => 'Giải Cờ Chớp Mùa Hè', 'format' => 'swiss']);

        $g = TournamentGroup::create(['tournament_id' => $t1->id, 'name' => 'Bảng A']);
        Participant::create(['tournament_id' => $t1->id, 'group_id' => $g->id, 'name' => 'Kỳ thủ 1']);

        $response = $this->get('/tournaments');
        $response->assertStatus(200);

        Livewire::test(TournamentList::class)
            ->assertSee('Giải Vô Địch Mùa Xuân')
            ->assertSee('Giải Cờ Chớp Mùa Hè')
            ->assertSee('1 Kỳ thủ');
    }

    public function test_tournament_list_search_filters_results(): void
    {
        Tournament::create(['name' => 'Giải Cờ Vua Hà Nội', 'format' => 'round_robin_groups']);
        Tournament::create(['name' => 'Giải Cờ Vua Sài Gòn', 'format' => 'swiss']);

        Livewire::test(TournamentList::class)
            ->set('search', 'Hà Nội')
            ->assertSee('Giải Cờ Vua Hà Nội')
            ->assertDontSee('Giải Cờ Vua Sài Gòn');
    }

    public function test_tournament_can_be_deleted(): void
    {
        $tournament = Tournament::create(['name' => 'Giải Cần Xóa', 'format' => 'swiss']);

        Livewire::test(TournamentList::class)
            ->call('deleteTournament', $tournament->id)
            ->assertSee('Đã xóa giải đấu Giải Cần Xóa.');

        $this->assertDatabaseMissing('tournaments', [
            'id' => $tournament->id,
        ]);
    }

    public function test_tournament_can_be_renamed_from_list(): void
    {
        $tournament = Tournament::create(['name' => 'Tên Cũ', 'format' => 'swiss']);

        Livewire::test(TournamentList::class)
            ->call('startEditing', $tournament->id, 'Tên Cũ')
            ->set('editingName', 'Tên Mới Sau Khi Đổi')
            ->call('saveName')
            ->assertSee('Đã đổi tên giải đấu thành công.');

        $tournament->refresh();
        $this->assertEquals('Tên Mới Sau Khi Đổi', $tournament->name);
    }
}
