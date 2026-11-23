<?php

namespace App\Livewire\Tournament;

use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Services\Scheduling\KnockoutSchedulerService;
use Livewire\Attributes\On;
use Livewire\Component;

class PlayoffBoard extends Component
{
    public int $tournamentId;
    public int $advancingCount = 4;

    public bool $isEditingHeader = false;
    public string $editingTitle = '';
    public string $editingDescription = '';

    public array $knockoutGames = [
        ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
        ['game_number' => 2, 'base_minutes' => 15, 'increment_seconds' => 2],
    ];

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $tournament = Tournament::findOrFail($tournamentId);
        $this->advancingCount = $tournament->advancing_count ?: 4;
    }

    public function startEditingHeader(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $t = Tournament::findOrFail($this->tournamentId);
        $this->editingTitle = $t->playoff_title ?: 'Vòng Loại Trực Tiếp (Playoff)';
        $this->editingDescription = $t->playoff_description ?: 'Các kỳ thủ đầu bảng gặp nhau theo thể thức đấu loại trực tiếp để tranh Cúp Vô Địch và Hạng Ba.';
        $this->isEditingHeader = true;
    }

    public function saveHeader(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $this->validate([
            'editingTitle' => 'required|string|max:255',
            'editingDescription' => 'nullable|string|max:1000',
        ]);

        $t = Tournament::findOrFail($this->tournamentId);
        $t->playoff_title = trim($this->editingTitle);
        $t->playoff_description = trim((string) $this->editingDescription);
        $t->save();

        $this->isEditingHeader = false;
        session()->flash('playoff_msg', 'Đã cập nhật tiêu đề và mô tả Vòng loại trực tiếp!');
    }

    public function cancelEditingHeader(): void
    {
        $this->isEditingHeader = false;
    }

    public function addKnockoutGame(): void
    {
        $nextNum = count($this->knockoutGames) + 1;
        $this->knockoutGames[] = [
            'game_number' => $nextNum,
            'base_minutes' => 5,
            'increment_seconds' => 2,
        ];
    }

    public function removeKnockoutGame(int $index): void
    {
        if (count($this->knockoutGames) > 1) {
            unset($this->knockoutGames[$index]);
            $this->knockoutGames = array_values($this->knockoutGames);
            foreach ($this->knockoutGames as $i => &$g) {
                $g['game_number'] = $i + 1;
            }
        }
    }

    public function updateAdvancingCount(int $count): void
    {
        if (! session('is_admin')) {
            return;
        }
        $this->advancingCount = $count;
        $tournament = Tournament::findOrFail($this->tournamentId);
        $tournament->advancing_count = $count;
        $tournament->save();
        session()->flash('playoff_msg', "Đã cập nhật: Lấy Top {$count} mỗi bảng vào Vòng loại trực tiếp.");
    }

    public function startKnockoutStage(KnockoutSchedulerService $scheduler): void
    {
        if (! session('is_admin')) {
            return;
        }
        $tournament = Tournament::findOrFail($this->tournamentId);
        $scheduler->generateQuarterFinals($tournament, $this->knockoutGames);
        $this->dispatch('match-updated');
        session()->flash('playoff_msg', 'Đã bốc thăm xong các cặp đấu Vòng loại trực tiếp!');
    }

    public function generateNextRound(KnockoutSchedulerService $scheduler, int $prevRoundId): void
    {
        if (! session('is_admin')) {
            return;
        }
        $tournament = Tournament::findOrFail($this->tournamentId);
        $prevRound = Round::findOrFail($prevRoundId);

        if ($prevRound->stage_name === 'Tứ Kết') {
            $newRound = $scheduler->generateSemiFinals($tournament, $prevRound, $this->knockoutGames);
            if ($newRound) {
                session()->flash('playoff_msg', 'Đã khởi tạo xong các cặp đấu Bán Kết!');
            } else {
                session()->flash('playoff_error', 'Chưa thể bốc thăm Bán Kết: Cần chốt điểm và phân định thắng thua ở tất cả các trận Tứ Kết trước.');
            }
        } elseif ($prevRound->stage_name === 'Bán Kết') {
            $newRound = $scheduler->generateFinals($tournament, $prevRound, $this->knockoutGames);
            if ($newRound) {
                session()->flash('playoff_msg', 'Đã khởi tạo xong trận Chung Kết và Trận Tranh Hạng 3!');
            } else {
                session()->flash('playoff_error', 'Chưa thể bốc thăm Chung Kết: Cần chốt điểm ở tất cả các trận Bán Kết trước.');
            }
        }

        $this->dispatch('match-updated');
    }

    public function resetKnockoutStage(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $tournament = Tournament::findOrFail($this->tournamentId);
        $tournament->rounds()->where('stage', 'knockout')->delete();
        $this->dispatch('match-updated');
        session()->flash('playoff_msg', 'Đã xóa và làm mới nhánh đấu Playoff.');
    }

    #[On('match-updated')]
    public function refreshPlayoff(): void
    {
        // Re-renders automatically
    }

    public function render()
    {
        $tournament = Tournament::with(['groups', 'standings.participant'])
            ->findOrFail($this->tournamentId);

        $knockoutRounds = $tournament->rounds()
            ->where('stage', 'knockout')
            ->orderBy('round_number')
            ->with(['matches.player1', 'matches.player2', 'matches.games'])
            ->get();

        $groups = $tournament->groups->values();
        $advancingPreview = [];
        foreach ($groups as $g) {
            $advancingPreview[$g->name] = Standing::with('participant')
                ->where('tournament_id', $tournament->id)
                ->where('group_id', $g->id)
                ->orderByDesc('points')
                ->orderByDesc('tie_break_score')
                ->orderByDesc('won')
                ->limit($this->advancingCount)
                ->get();
        }

        return view('livewire.tournament.playoff-board', [
            'tournament' => $tournament,
            'knockoutRounds' => $knockoutRounds,
            'advancingPreview' => $advancingPreview,
            'isAdmin' => (bool) session('is_admin'),
        ]);
    }
}
