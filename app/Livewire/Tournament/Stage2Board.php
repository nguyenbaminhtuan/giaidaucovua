<?php

namespace App\Livewire\Tournament;

use App\Models\Round;
use App\Models\Standing;
use App\Models\Tournament;
use App\Services\Scheduling\Stage2SchedulerService;
use Livewire\Attributes\On;
use Livewire\Component;

class Stage2Board extends Component
{
    public int $tournamentId;
    public int $selectedRoundNumber = 1;
    public string $activeSubTab = 'round_robin'; // 'round_robin' | 'combined_standings'

    public float $bonusTop1 = 4.0;
    public float $bonusTop2 = 3.0;
    public float $bonusTop3 = 2.0;

    public array $timeControls = [
        ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
        ['game_number' => 2, 'base_minutes' => 5, 'increment_seconds' => 2],
    ];

    public function mount(int $tournamentId): void
    {
        $this->tournamentId = $tournamentId;
        $t = Tournament::findOrFail($tournamentId);
        $this->bonusTop1 = (float) ($t->bonus_top1 ?: 4.0);
        $this->bonusTop2 = (float) ($t->bonus_top2 ?: 3.0);
        $this->bonusTop3 = (float) ($t->bonus_top3 ?: 2.0);
    }

    public function selectRound(int $roundNum): void
    {
        $this->selectedRoundNumber = $roundNum;
    }

    public function switchSubTab(string $tab): void
    {
        $this->activeSubTab = $tab;
    }

    public function addGame(): void
    {
        $next = count($this->timeControls) + 1;
        $this->timeControls[] = [
            'game_number' => $next,
            'base_minutes' => 5,
            'increment_seconds' => 2,
        ];
    }

    public function removeGame(int $index): void
    {
        if (count($this->timeControls) > 1) {
            unset($this->timeControls[$index]);
            $this->timeControls = array_values($this->timeControls);
            foreach ($this->timeControls as $i => &$g) {
                $g['game_number'] = $i + 1;
            }
        }
    }

    public function saveBonusConfig(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $t = Tournament::findOrFail($this->tournamentId);
        $t->bonus_top1 = $this->bonusTop1;
        $t->bonus_top2 = $this->bonusTop2;
        $t->bonus_top3 = $this->bonusTop3;
        $t->save();

        app(Stage2SchedulerService::class)->syncBonusPoints($t);
        $this->dispatch('match-updated');
        session()->flash('stage2_msg', 'Đã cập nhật mức điểm thưởng thành công!');
    }

    public function generateStage2Rounds(Stage2SchedulerService $scheduler): void
    {
        if (! session('is_admin')) {
            return;
        }
        $t = Tournament::findOrFail($this->tournamentId);
        $rounds = $scheduler->generateStage2RoundRobin($t, $this->timeControls);

        if ($rounds->isNotEmpty()) {
            $this->dispatch('match-updated');
            session()->flash('stage2_msg', 'Đã khởi tạo xong 7 vòng đấu cho 8 kỳ thủ!');
        } else {
            session()->flash('stage2_error', 'Chưa đủ 8 kỳ thủ đi tiếp từ vòng bảng!');
        }
    }

    public function promoteToStepladder(Stage2SchedulerService $scheduler): void
    {
        if (! session('is_admin')) {
            return;
        }
        $t = Tournament::findOrFail($this->tournamentId);
        $round = $scheduler->promoteToStepladder($t, $this->timeControls);

        if ($round) {
            $this->dispatch('match-updated');
            $this->dispatch('switch-to-stepladder');
            session()->flash('stage2_msg', 'Đã bốc thăm Top 5 vào Vòng Chung Kết Stepladder!');
        } else {
            session()->flash('stage2_error', 'Chưa đủ 5 kỳ thủ từ bảng xếp hạng tổng hợp!');
        }
    }

    #[On('match-updated')]
    public function refreshStage2(): void
    {
        // Re-renders automatically
    }

    public function render()
    {
        $tournament = Tournament::findOrFail($this->tournamentId);

        $rounds = $tournament->rounds()
            ->where('stage', 'stage2_round_robin')
            ->orderBy('round_number')
            ->with(['matches.player1', 'matches.player2', 'matches.games'])
            ->get();

        $activeRound = $rounds->firstWhere('round_number', $this->selectedRoundNumber) ?? $rounds->first();
        $matches = $activeRound ? $activeRound->matches : collect();

        $standings = Standing::with('participant')
            ->where('tournament_id', $tournament->id)
            ->where('stage', 'stage2_round_robin')
            ->get()
            ->sortByDesc(fn ($st) => $st->total_points * 1000 + $st->tie_break_score)
            ->values();

        return view('livewire.tournament.stage2-board', [
            'tournament' => $tournament,
            'rounds' => $rounds,
            'activeRound' => $activeRound,
            'matches' => $matches,
            'standings' => $standings,
            'isAdmin' => (bool) session('is_admin'),
        ]);
    }
}
