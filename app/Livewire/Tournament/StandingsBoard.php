<?php

namespace App\Livewire\Tournament;

use App\Models\Participant;
use App\Models\Standing;
use App\Models\Tournament;
use App\Services\Standings\StandingsCalculatorService;
use Livewire\Attributes\On;
use Livewire\Component;

class StandingsBoard extends Component
{
    public int $tournamentId;
    public ?int $selectedGroupId = null;

    public function mount(int $tournamentId, ?int $selectedGroupId = null): void
    {
        $this->tournamentId = $tournamentId;
        $this->selectedGroupId = $selectedGroupId;
    }

    public function selectGroup(?int $groupId): void
    {
        $this->selectedGroupId = $groupId;
    }

    #[On('match-updated')]
    public function refreshStandings(): void
    {
        // Re-renders automatically
    }

    public function render(StandingsCalculatorService $calculator)
    {
        $tournament = Tournament::with('groups')->findOrFail($this->tournamentId);

        if ($tournament->format === 'round_robin_groups' && ! $this->selectedGroupId) {
            $this->selectedGroupId = $tournament->groups->first()?->id;
        }

        // Query standings
        $query = Standing::with('participant')
            ->where('tournament_id', $tournament->id);

        if ($tournament->format === 'round_robin_groups' && $this->selectedGroupId) {
            $query->where('group_id', $this->selectedGroupId);
        }

        $standings = $query->orderByDesc('points')
            ->orderByDesc('tie_break_score')
            ->orderByDesc('won')
            ->get();

        // If standings table is still empty (tournament just initialized), populate default rows
        if ($standings->isEmpty()) {
            $partsQuery = Participant::where('tournament_id', $tournament->id);
            if ($tournament->format === 'round_robin_groups' && $this->selectedGroupId) {
                $partsQuery->where('group_id', $this->selectedGroupId);
            }
            $defaultRows = $partsQuery->get()->map(function ($p, $idx) {
                return (object) [
                    'participant' => $p,
                    'played' => 0,
                    'won' => 0,
                    'drawn' => 0,
                    'lost' => 0,
                    'points' => 0.0,
                    'tie_break_score' => 0.0,
                ];
            });
        } else {
            $defaultRows = $standings;
        }

        return view('livewire.tournament.standings-board', [
            'tournament' => $tournament,
            'rows' => $defaultRows,
        ]);
    }
}
