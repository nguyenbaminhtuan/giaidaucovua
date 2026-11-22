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

    // Delete confirmation with PIN
    public ?int $deletingTournamentId = null;
    public string $deletingTournamentName = '';
    public string $deletePinInput = '';

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

    public function confirmDelete(int $id, string $name): void
    {
        $this->deletingTournamentId = $id;
        $this->deletingTournamentName = $name;
        $this->deletePinInput = '';
        $this->resetErrorBag();
    }

    public function cancelDelete(): void
    {
        $this->deletingTournamentId = null;
        $this->deletingTournamentName = '';
        $this->deletePinInput = '';
        $this->resetErrorBag();
    }

    public function executeDelete(): void
    {
        $correctPin = (string) env('ADMIN_DELETE_PIN', '2026');

        if (trim($this->deletePinInput) !== $correctPin) {
            $this->addError('deletePinInput', 'Mã PIN bảo vệ không chính xác. Không thể xóa giải.');
            return;
        }

        $tournament = Tournament::find($this->deletingTournamentId);
        if ($tournament) {
            $tournamentName = $tournament->name;
            $tournament->delete();
            session()->flash('message', "Đã xóa vĩnh viễn giải đấu {$tournamentName}.");
        }

        $this->cancelDelete();
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
