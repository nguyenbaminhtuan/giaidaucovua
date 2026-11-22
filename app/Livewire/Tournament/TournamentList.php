<?php

namespace App\Livewire\Tournament;

use App\Models\Tournament;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Danh Sách Giải Đấu Cờ Vua')]
class TournamentList extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingTournamentId = null;
    public string $editingName = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function startEditing(int $id, string $currentName): void
    {
        $this->editingTournamentId = $id;
        $this->editingName = $currentName;
    }

    public function saveName(): void
    {
        $this->validate([
            'editingName' => 'required|string|max:255',
        ]);

        $tournament = Tournament::find($this->editingTournamentId);
        if ($tournament) {
            $tournament->name = trim($this->editingName);
            $tournament->save();
            session()->flash('message', 'Đã đổi tên giải đấu thành công.');
        }

        $this->editingTournamentId = null;
    }

    public function cancelEditing(): void
    {
        $this->editingTournamentId = null;
    }

    public function deleteTournament(int $id): void
    {
        $tournament = Tournament::find($id);
        if ($tournament) {
            $tournament->delete();
            session()->flash('message', "Đã xóa giải đấu {$tournament->name}.");
        }
    }

    public function render()
    {
        $tournaments = Tournament::query()
            ->withCount(['participants', 'groups', 'rounds'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('livewire.tournament.tournament-list', [
            'tournaments' => $tournaments,
        ]);
    }
}
