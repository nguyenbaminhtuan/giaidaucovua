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
        if (! session('is_admin')) {
            return;
        }

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

    public function selectWinner(int $participantId): void
    {
        if (! session('is_admin')) {
            return;
        }

        $match = ChessMatch::findOrFail($this->matchId);
        if ($match->status === 'completed' || $match->is_bye) {
            return;
        }

        $match->winner_id = $participantId;
        $match->save();

        $this->dispatch('match-updated');
    }

    public function addTieBreakGame(): void
    {
        if (! session('is_admin')) {
            return;
        }

        $match = ChessMatch::with('games')->findOrFail($this->matchId);
        if ($match->status === 'completed' || $match->is_bye) {
            return;
        }

        $lastGame = $match->games->last();
        $nextNum = ($match->games->max('game_number') ?? 0) + 1;

        $whiteId = $lastGame ? $lastGame->black_player_id : $match->player1_id;
        $blackId = $lastGame ? $lastGame->white_player_id : $match->player2_id;
        $baseMinutes = $lastGame ? $lastGame->base_minutes : 3;
        $incrementSec = $lastGame ? $lastGame->increment_seconds : 2;

        ChessGame::create([
            'match_id' => $match->id,
            'game_number' => $nextNum,
            'base_minutes' => $baseMinutes,
            'increment_seconds' => $incrementSec,
            'white_player_id' => $whiteId,
            'black_player_id' => $blackId,
            'result' => null,
            'status' => 'pending',
            'is_tie_break' => true,
        ]);

        $this->isOpen = true;
        $this->dispatch('match-updated');
    }

    public function confirmMatch(): void
    {
        if (! session('is_admin')) {
            return;
        }

        $match = ChessMatch::with(['round', 'games'])->findOrFail($this->matchId);

        if (! $match->areAllGamesFinished()) {
            return;
        }

        // Knockout matches must have a decisive winner
        if ($match->round?->isKnockout() && $match->isTied() && ! $match->winner_id) {
            session()->flash('match_error', 'Trận đấu đang hòa điểm. Hãy chọn người thắng theo BXH hoặc đấu thêm ván phụ!');
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
        if (! session('is_admin')) {
            return;
        }

        $match = ChessMatch::findOrFail($this->matchId);
        $match->status = 'pending';
        $match->save();

        MatchResultConfirmed::dispatch($match);
        $this->dispatch('match-updated');
    }

    public function render()
    {
        $match = ChessMatch::with(['round.group', 'player1', 'player2', 'winner', 'games.whitePlayer', 'games.blackPlayer'])
            ->findOrFail($this->matchId);

        return view('livewire.tournament.match-row', [
            'match' => $match,
            'isAdmin' => (bool) session('is_admin'),
        ]);
    }
}
