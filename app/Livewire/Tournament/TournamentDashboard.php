<?php

namespace App\Livewire\Tournament;

use App\Models\ChessMatch;
use App\Models\Round;
use App\Models\Tournament;
use App\Models\TournamentGroup;
use App\Services\Scheduling\StepladderSchedulerService;
use App\Services\Scheduling\SwissSchedulerService;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Chi Tiết Giải Đấu')]
class TournamentDashboard extends Component
{
    public int $tournamentId;
    public string $activeTab = 'matches'; // 'matches' | 'playoff' | 'standings'
    public ?int $selectedGroupId = null;
    public int $selectedRoundNumber = 1;

    public bool $isEditingName = false;
    public string $editingName = '';

    public string $matchSearch = '';

    public function mount(Tournament $tournament): void
    {
        $this->tournamentId = $tournament->id;

        if ($tournament->format === 'round_robin_groups') {
            $firstGroup = $tournament->groups()->first();
            $this->selectedGroupId = $firstGroup?->id;
        }
    }

    public function clearMatchSearch(): void
    {
        $this->matchSearch = '';
    }

    public function startEditingName(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $tournament = Tournament::findOrFail($this->tournamentId);
        $this->editingName = $tournament->name;
        $this->isEditingName = true;
    }

    public function saveName(): void
    {
        if (! session('is_admin')) {
            return;
        }
        $this->validate([
            'editingName' => 'required|string|max:255',
        ]);

        $tournament = Tournament::findOrFail($this->tournamentId);
        $tournament->name = trim($this->editingName);
        $tournament->save();

        $this->isEditingName = false;
        session()->flash('name_updated', 'Đã cập nhật tên giải đấu thành công.');
    }

    public function cancelEditingName(): void
    {
        $this->isEditingName = false;
    }

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function selectGroup(?int $groupId): void
    {
        $this->selectedGroupId = $groupId;
        $this->selectedRoundNumber = 1;
        $this->matchSearch = '';
    }

    public function selectRound(int $roundNumber): void
    {
        $this->selectedRoundNumber = $roundNumber;
        $this->matchSearch = '';
    }

    public function generateNextSwissRound(SwissSchedulerService $swissScheduler): void
    {
        if (! session('is_admin')) {
            return;
        }
        $tournament = Tournament::with(['rounds.matches'])->findOrFail($this->tournamentId);
        $maxRound = $tournament->rounds->max('round_number') ?? 0;
        $nextRoundNum = $maxRound + 1;

        $swissScheduler->generateNextRound($tournament, $nextRoundNum);
        $this->selectedRoundNumber = $nextRoundNum;
        $this->dispatch('match-updated');
    }

    public function generateStepladderFinals(StepladderSchedulerService $stepladderScheduler): void
    {
        if (! session('is_admin')) {
            return;
        }

        $tournament = Tournament::with(['rounds.matches.games'])->findOrFail($this->tournamentId);
        $r1 = $tournament->rounds()->where('round_number', 1)->first();

        if ($r1) {
            $finalsRound = $stepladderScheduler->generateFinalsRound($tournament, $r1, $tournament->timeControls);
            if ($finalsRound) {
                $this->selectedRoundNumber = $finalsRound->round_number;
                $this->dispatch('match-updated');
                session()->flash('dashboard_msg', 'Đã khởi tạo xong Trận Tranh HCV và Tranh HCĐ!');
            } else {
                session()->flash('dashboard_error', 'Chưa thể tạo Trận Chung Kết: Cần chốt điểm và xác định người thắng ở Trận 1 và Trận 2 trước.');
            }
        }
    }

    #[On('match-updated')]
    public function refreshData(): void
    {
        // Re-renders automatically
    }

    public function render()
    {
        $tournament = Tournament::with(['groups', 'rounds.matches'])
            ->findOrFail($this->tournamentId);

        $isSearching = ! empty(trim($this->matchSearch));

        if ($isSearching) {
            $searchTerm = '%' . trim($this->matchSearch) . '%';
            $matches = ChessMatch::whereHas('round', fn ($q) => $q->where('tournament_id', $tournament->id))
                ->where(function ($query) use ($searchTerm) {
                    $query->whereHas('player1', fn ($q) => $q->where('name', 'like', $searchTerm))
                        ->orWhereHas('player2', fn ($q) => $q->where('name', 'like', $searchTerm));
                })
                ->with(['round.group', 'player1', 'player2'])
                ->get();

            $rounds = collect();
            $activeRound = null;
        } else {
            $roundsQuery = Round::where('tournament_id', $tournament->id);
            if ($tournament->format === 'round_robin_groups' && $this->selectedGroupId) {
                $roundsQuery->where('group_id', $this->selectedGroupId);
            }
            $rounds = $roundsQuery->orderBy('round_number')->get();

            $activeRound = $rounds->firstWhere('round_number', $this->selectedRoundNumber) ?? $rounds->first();
            $matches = $activeRound ? $activeRound->matches()->with(['round.group', 'player1', 'player2'])->get() : collect();
        }

        return view('livewire.tournament.tournament-dashboard', [
            'tournament' => $tournament,
            'rounds' => $rounds,
            'activeRound' => $activeRound,
            'matches' => $matches,
            'isSearching' => $isSearching,
        ]);
    }
}
