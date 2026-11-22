<?php

namespace App\Livewire\Tournament;

use App\Events\MatchResultConfirmed;
use App\Models\ChessGame;
use App\Models\ChessMatch;
use Livewire\Component;

class MatchRow extends Component
{
    public int $matchId;
    public bool $isOpen = false;
    public bool $showContext = false;

    public function toggleOpen(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    public function setGameResult(int $gameId, string $result): void
    {
        $match = ChessMatch::with('games')->findOrFail($this->matchId);

        if ($match->status === 'completed' || $match->is_bye) {
            return;
        }

        $game = $match->games->firstWhere('id', $gameId);
        if ($game) {
            $game->result = $result;
            $game->status = 'completed';
            $game->save();

            $match->recalculateScores();
        }
    }

    public function confirmMatch(): void
    {
        $match = ChessMatch::with('games')->findOrFail($this->matchId);

        if (! $match->areAllGamesFinished()) {
            return;
        }

        $match->status = 'completed';
        $match->recalculateScores();
        $match->save();

        MatchResultConfirmed::dispatch($match);
        $this->dispatch('match-updated');
    }

    public function unlockMatch(): void
    {
        $match = ChessMatch::findOrFail($this->matchId);
        $match->status = 'pending';
        $match->save();

        MatchResultConfirmed::dispatch($match);
        $this->dispatch('match-updated');
    }

    public function render()
    {
        $match = ChessMatch::with(['round.group', 'player1', 'player2', 'games.whitePlayer', 'games.blackPlayer'])
            ->findOrFail($this->matchId);

        return view('livewire.tournament.match-row', [
            'match' => $match,
        ]);
    }
}
