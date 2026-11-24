<?php

namespace App\Livewire\Tournament;

use App\Services\TournamentCreationService;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Thiết Lập Giải Đấu Cờ Vua')]
class SetupWizard extends Component
{
    public string $name = 'Vòng Chung Kết Top 5';
    public string $format = 'round_robin_groups'; // 'round_robin_groups', 'stepladder_top5', 'swiss'
    public int $advancing_count = 4;

    public array $groups = [
        [
            'name' => 'Bảng A',
            'players_text' => "Nguyễn Văn A\nTrần Văn B\nLê Văn C\nPhạm Văn D\nHoàng Văn E",
            'games' => [
                ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
                ['game_number' => 2, 'base_minutes' => 15, 'increment_seconds' => 2],
            ],
        ],
        [
            'name' => 'Bảng B',
            'players_text' => "Vũ Văn F\nĐặng Văn G\nBùi Văn H\nĐỗ Văn I",
            'games' => [
                ['game_number' => 1, 'base_minutes' => 10, 'increment_seconds' => 5],
            ],
        ],
    ];

    public array $stepladder_players = [
        0 => 'Kỳ thủ Hạng 1 (Chờ ở Chung Kết)',
        1 => 'Kỳ thủ Hạng 2',
        2 => 'Kỳ thủ Hạng 3',
        3 => 'Kỳ thủ Hạng 4',
        4 => 'Kỳ thủ Hạng 5',
    ];

    public string $swiss_players_text = "Nguyễn Văn A\nTrần Văn B\nLê Văn C\nPhạm Văn D\nHoàng Văn E\nVũ Văn F";

    public array $swiss_games = [
        ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
        ['game_number' => 2, 'base_minutes' => 5, 'increment_seconds' => 2],
        ['game_number' => 3, 'base_minutes' => 5, 'increment_seconds' => 2],
    ];

    public function mount()
    {
        if (! session('is_admin')) {
            session()->flash('auth_required', 'Bạn cần nhập mã PIN quản trị (1210) để tạo giải đấu mới.');
            return $this->redirect(route('home'), navigate: true);
        }
    }

    public function addGroup(): void
    {
        $letters = range('A', 'Z');
        $nextIndex = count($this->groups);
        $letter = $letters[$nextIndex] ?? ((string) ($nextIndex + 1));

        $this->groups[] = [
            'name' => "Bảng {$letter}",
            'players_text' => '',
            'games' => [
                ['game_number' => 1, 'base_minutes' => 5, 'increment_seconds' => 2],
            ],
        ];
    }

    public function removeGroup(int $index): void
    {
        if (count($this->groups) > 1) {
            unset($this->groups[$index]);
            $this->groups = array_values($this->groups);
        }
    }

    public function addGroupGame(int $groupIndex): void
    {
        $nextNum = count($this->groups[$groupIndex]['games']) + 1;
        $this->groups[$groupIndex]['games'][] = [
            'game_number' => $nextNum,
            'base_minutes' => 5,
            'increment_seconds' => 0,
        ];
    }

    public function removeGroupGame(int $groupIndex, int $gameIndex): void
    {
        if (count($this->groups[$groupIndex]['games']) > 1) {
            unset($this->groups[$groupIndex]['games'][$gameIndex]);
            $this->groups[$groupIndex]['games'] = array_values($this->groups[$groupIndex]['games']);
            foreach ($this->groups[$groupIndex]['games'] as $i => &$g) {
                $g['game_number'] = $i + 1;
            }
        }
    }

    public function addSwissGame(): void
    {
        $nextNum = count($this->swiss_games) + 1;
        $this->swiss_games[] = [
            'game_number' => $nextNum,
            'base_minutes' => 5,
            'increment_seconds' => 0,
        ];
    }

    public function removeSwissGame(int $gameIndex): void
    {
        if (count($this->swiss_games) > 1) {
            unset($this->swiss_games[$gameIndex]);
            $this->swiss_games = array_values($this->swiss_games);
            foreach ($this->swiss_games as $i => &$g) {
                $g['game_number'] = $i + 1;
            }
        }
    }

    public function confirmAndGenerate(TournamentCreationService $creationService)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'format' => 'required|in:round_robin_groups,stepladder_top5,swiss',
        ];

        if ($this->format === 'round_robin_groups') {
            $rules['advancing_count'] = 'required|integer|min:1|max:8';
            $rules['groups'] = 'required|array|min:1';
            $rules['groups.*.name'] = 'required|string|max:100';
            $rules['groups.*.games'] = 'required|array|min:1';
            $rules['groups.*.games.*.base_minutes'] = 'required|integer|min:1|max:180';
            $rules['groups.*.games.*.increment_seconds'] = 'required|integer|min:0|max:60';
        } elseif ($this->format === 'stepladder_top5') {
            $rules['stepladder_players'] = 'required|array|size:5';
            $rules['stepladder_players.*'] = 'required|string|max:100';
            $rules['swiss_games'] = 'required|array|min:1';
            $rules['swiss_games.*.base_minutes'] = 'required|integer|min:1|max:180';
            $rules['swiss_games.*.increment_seconds'] = 'required|integer|min:0|max:60';
        } else {
            $rules['swiss_games'] = 'required|array|min:1';
            $rules['swiss_games.*.base_minutes'] = 'required|integer|min:1|max:180';
            $rules['swiss_games.*.increment_seconds'] = 'required|integer|min:0|max:60';
        }

        $this->validate($rules);

        $tournament = $creationService->create(
            $this->name,
            $this->format,
            $this->groups,
            $this->swiss_games,
            $this->swiss_players_text,
            $this->advancing_count,
            $this->stepladder_players
        );

        return $this->redirect(route('tournament.show', $tournament), navigate: true);
    }

    public function render()
    {
        return view('livewire.tournament.setup-wizard');
    }
}
