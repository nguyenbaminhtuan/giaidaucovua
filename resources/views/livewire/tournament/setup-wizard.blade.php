<div class="max-w-5xl mx-auto space-y-8">
    <div class="border-b border-slate-800 pb-4">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <span>⚙️</span> Thiết Lập Thể Thức Giải Đấu
        </h1>
        <p class="text-sm text-slate-400 mt-1">Cấu hình thể thức thi đấu, chia bảng, số ván và thời gian kiểm soát riêng biệt cho từng bảng.</p>
    </div>

    <form wire:submit="confirmAndGenerate" class="space-y-6">
        <!-- Tournament Name -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-2">
            <label class="block text-sm font-semibold text-slate-200">Tên Giải Đấu</label>
            <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-amber-500 transition" placeholder="Ví dụ: Giải Vô Địch Cờ Vua Mùa Xuân 2026">
            @error('name') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Format Selection -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
            <label class="block text-sm font-semibold text-slate-200">Thể Thức Thi Đấu</label>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition {{ $format === 'round_robin_groups' ? 'border-amber-500 bg-amber-500/10' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' }}">
                    <input type="radio" wire:model.live="format" value="round_robin_groups" class="mt-1 text-amber-500 focus:ring-amber-500">
                    <div>
                        <span class="font-bold text-white block">Chia Bảng Đấu Vòng Tròn (Round-Robin)</span>
                        <span class="text-xs text-slate-400 block mt-1">Mỗi bảng có thể tùy chỉnh danh sách kỳ thủ, số ván và thời gian thi đấu riêng biệt. Tự động xử lý Bye nếu bảng lẻ người.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-lg border cursor-pointer transition {{ $format === 'swiss' ? 'border-amber-500 bg-amber-500/10' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' }}">
                    <input type="radio" wire:model.live="format" value="swiss" class="mt-1 text-amber-500 focus:ring-amber-500">
                    <div>
                        <span class="font-bold text-white block">Hệ Thụy Sĩ (Swiss System)</span>
                        <span class="text-xs text-slate-400 block mt-1">Ghép cặp theo nhóm điểm, chống gặp lại đối thủ cũ, cân bằng màu quân Trắng/Đen qua từng vòng đấu.</span>
                    </div>
                </label>
            </div>
        </div>

        <!-- Groups or Swiss -->
        @if ($format === 'round_robin_groups')
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-bold text-white">Cấu Hình Từng Bảng Đấu</h2>
                        <p class="text-xs text-slate-400">Mỗi bảng có danh sách kỳ thủ, số ván mỗi cặp và thời gian kiểm soát riêng.</p>
                    </div>
                    <button type="button" wire:click="addGroup" class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                        + Thêm Bảng Mới
                    </button>
                </div>

                <div class="space-y-6">
                    @foreach ($groups as $idx => $grp)
                        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-5 space-y-4 relative">
                            <!-- Group Header -->
                            <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-amber-400 font-bold text-base">🗂</span>
                                    <input type="text"
                                           wire:model="groups.{{ $idx }}.name"
                                           class="font-bold text-base bg-slate-950 border border-slate-700 rounded-lg px-3 py-1 text-amber-400 focus:outline-none focus:border-amber-500 w-36">
                                </div>

                                @if (count($groups) > 1)
                                    <button type="button" wire:click="removeGroup({{ $idx }})" class="text-xs text-rose-400 hover:text-rose-300 transition">
                                        Xóa bảng này
                                    </button>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                <!-- Participants in Group (5 cols) -->
                                <div class="lg:col-span-5 space-y-2">
                                    <label class="text-xs font-semibold text-slate-300 block">
                                        Kỳ thủ trong {{ $grp['name'] }} (1 tên / 1 dòng):
                                    </label>
                                    <textarea wire:model="groups.{{ $idx }}.players_text" rows="6" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500 font-mono" placeholder="Nguyễn Văn A&#10;Trần Văn B&#10;Lê Văn C"></textarea>
                                </div>

                                <!-- Time Controls & Number of Games for THIS Group (7 cols) -->
                                <div class="lg:col-span-7 space-y-3 bg-slate-950/60 p-4 rounded-xl border border-slate-800/80">
                                    <div class="flex items-center justify-between">
                                        <div class="text-xs font-bold text-slate-200">
                                            ⏱ Số ván & Thời gian thi đấu của {{ $grp['name'] }}
                                        </div>
                                        <button type="button" wire:click="addGroupGame({{ $idx }})" class="px-2.5 py-1 text-xs font-semibold rounded bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                                            + Thêm Ván
                                        </button>
                                    </div>

                                    <div class="space-y-2.5">
                                        @foreach ($grp['games'] as $gIdx => $g)
                                            <div class="flex flex-wrap items-center justify-between gap-3 p-2.5 bg-slate-900 border border-slate-800 rounded-lg text-xs">
                                                <div class="flex items-center gap-2">
                                                    <span class="w-5 h-5 rounded-full bg-amber-500/10 text-amber-400 font-bold flex items-center justify-center border border-amber-500/20">
                                                        {{ $g['game_number'] }}
                                                    </span>
                                                    <span class="font-medium text-white">Ván {{ $g['game_number'] }}</span>
                                                </div>

                                                <div class="flex items-center gap-3">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400">Gốc:</span>
                                                        <input type="number" min="1" max="180" wire:model="groups.{{ $idx }}.games.{{ $gIdx }}.base_minutes" class="w-14 bg-slate-950 border border-slate-700 rounded px-1.5 py-0.5 text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                                        <span class="text-slate-500">phút</span>
                                                    </div>

                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-slate-400">+</span>
                                                        <input type="number" min="0" max="60" wire:model="groups.{{ $idx }}.games.{{ $gIdx }}.increment_seconds" class="w-14 bg-slate-950 border border-slate-700 rounded px-1.5 py-0.5 text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                                        <span class="text-slate-500">giây</span>
                                                    </div>

                                                    @if (count($grp['games']) > 1)
                                                        <button type="button" wire:click="removeGroupGame({{ $idx }}, {{ $gIdx }})" class="text-rose-400 hover:text-rose-300 ml-1">
                                                            ✕
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <!-- Swiss System Setup -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-5">
                <div class="space-y-2">
                    <h2 class="text-base font-semibold text-white">Danh Sách Kỳ Thủ Hệ Thụy Sĩ</h2>
                    <p class="text-xs text-slate-400">Nhập danh sách tất cả kỳ thủ tham gia giải (mỗi kỳ thủ 1 dòng):</p>
                    <textarea wire:model="swiss_players_text" rows="6" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-200 focus:outline-none focus:border-amber-500 font-mono" placeholder="Kỳ thủ 1&#10;Kỳ thủ 2&#10;Kỳ thủ 3..."></textarea>
                </div>

                <!-- Swiss Time Controls -->
                <div class="space-y-3 border-t border-slate-800 pt-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Cấu Hình Số Ván & Thời Gian Mỗi Cặp (Hệ Thụy Sĩ)</h3>
                            <p class="text-xs text-slate-400">Thiết lập thời gian áp dụng chung cho các cặp đấu trong hệ Thụy Sĩ.</p>
                        </div>
                        <button type="button" wire:click="addSwissGame" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                            + Thêm Ván
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        @foreach ($swiss_games as $gIdx => $g)
                            <div class="flex flex-wrap items-center justify-between gap-4 p-3 bg-slate-950 border border-slate-800 rounded-lg">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-500/10 text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">
                                        {{ $g['game_number'] }}
                                    </span>
                                    <span class="font-medium text-sm text-white">Ván {{ $g['game_number'] }}</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-400">Gốc:</label>
                                        <div class="flex items-center">
                                            <input type="number" min="1" max="180" wire:model="swiss_games.{{ $gIdx }}.base_minutes" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                            <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">phút</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-400">+</label>
                                        <div class="flex items-center">
                                            <input type="number" min="0" max="60" wire:model="swiss_games.{{ $gIdx }}.increment_seconds" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                            <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">giây</span>
                                        </div>
                                    </div>

                                    @if (count($swiss_games) > 1)
                                        <button type="button" wire:click="removeSwissGame({{ $gIdx }})" class="text-xs text-rose-400 hover:text-rose-300 ml-2">
                                            Xóa
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Submit Button -->
        <div class="pt-2">
            <button type="submit" wire:loading.attr="disabled" class="w-full py-4 px-6 rounded-xl font-bold text-base bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-lg shadow-amber-500/10 flex items-center justify-center gap-3 transition">
                <span wire:loading.remove>✓ Xác Nhận & Tự Động Xếp Cặp Đấu</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-5 w-5 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    Đang thiết lập giải và bốc thăm...
                </span>
            </button>
        </div>
    </form>
</div>
