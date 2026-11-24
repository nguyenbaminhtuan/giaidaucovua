<div class="max-w-5xl mx-auto space-y-8">
    <div class="border-b border-slate-800 pb-4">
        <h1 class="text-2xl font-bold text-white flex items-center gap-2">
            <span>⚙️</span> Thiết Lập Thể Thức Giải Đấu
        </h1>
        <p class="text-sm text-slate-400 mt-1">Cấu hình thể thức thi đấu, chia bảng, vòng chung kết Top 5 hoặc hệ Thụy Sĩ.</p>
    </div>

    <form wire:submit="confirmAndGenerate" class="space-y-6">
        <!-- Tournament Name -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-2">
            <label class="block text-sm font-semibold text-slate-200">Tên Giải Đấu</label>
            <input type="text" wire:model="name" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-white focus:outline-none focus:border-amber-500 transition" placeholder="Ví dụ: Vòng Chung Kết Top 5 Tranh Cúp 2026">
            @error('name') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Format Selection -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-3">
            <label class="block text-sm font-semibold text-slate-200">Chọn Thể Thức Thi Đấu</label>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $format === 'round_robin_groups' ? 'border-amber-500 bg-amber-500/10' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' }}">
                    <input type="radio" wire:model.live="format" value="round_robin_groups" class="mt-1 text-amber-500 focus:ring-amber-500">
                    <div>
                        <span class="font-bold text-white block text-sm">Vòng Tròn Chia Bảng</span>
                        <span class="text-xs text-slate-400 block mt-1">Chia bảng A, B, C... thi đấu vòng tròn và chọn số người đầu bảng đi tiếp.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $format === 'stepladder_top5' ? 'border-amber-500 bg-amber-500/10' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' }}">
                    <input type="radio" wire:model.live="format" value="stepladder_top5" class="mt-1 text-amber-500 focus:ring-amber-500">
                    <div>
                        <span class="font-bold text-amber-400 block text-sm">Chung Kết Top 5 (Stepladder)</span>
                        <span class="text-xs text-slate-400 block mt-1">Dành cho 5 kỳ thủ: Trận 1 (Hạng 2 vs 3), Trận 2 (Hạng 4 vs 5), Tranh HCĐ và Tranh HCV.</span>
                    </div>
                </label>

                <label class="flex items-start gap-3 p-4 rounded-xl border cursor-pointer transition {{ $format === 'swiss' ? 'border-amber-500 bg-amber-500/10' : 'border-slate-800 bg-slate-950/50 hover:border-slate-700' }}">
                    <input type="radio" wire:model.live="format" value="swiss" class="mt-1 text-amber-500 focus:ring-amber-500">
                    <div>
                        <span class="font-bold text-white block text-sm">Hệ Thụy Sĩ (Swiss)</span>
                        <span class="text-xs text-slate-400 block mt-1">Ghép cặp theo nhóm điểm, chống gặp lại đối thủ cũ, cân bằng màu quân.</span>
                    </div>
                </label>
            </div>
        </div>

        @if ($format === 'round_robin_groups')
            <!-- Advancing Count Selection -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 space-y-2">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-200">
                            Số Lượng Người Mỗi Bảng Đi Tiếp Vào Vòng Loại Trực Tiếp (Playoff)
                        </label>
                        <p class="text-xs text-slate-400 mt-1">
                            Ví dụ chọn 4: Lấy Top 4 Bảng A và Top 4 Bảng B (tổng 8 người) bốc thăm chéo (1A vs 4B, 1B vs 4A...) thi đấu Tứ kết ➔ Bán kết ➔ Chung kết.
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <select wire:model="advancing_count" class="bg-slate-950 border border-slate-700 rounded-lg px-3 py-2 text-amber-400 font-bold text-sm focus:outline-none focus:border-amber-500">
                            <option value="1">Top 1 mỗi bảng (Vào Chung kết)</option>
                            <option value="2">Top 2 mỗi bảng (Vào Bán kết - 4 người)</option>
                            <option value="4">Top 4 mỗi bảng (Vào Tứ kết - 8 người)</option>
                        </select>
                    </div>
                </div>
            </div>

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
                                <div class="lg:col-span-5 space-y-2">
                                    <label class="text-xs font-semibold text-slate-300 block">
                                        Kỳ thủ trong {{ $grp['name'] }} (1 tên / 1 dòng):
                                    </label>
                                    <textarea wire:model="groups.{{ $idx }}.players_text" rows="6" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-sm text-slate-200 focus:outline-none focus:border-amber-500 font-mono" placeholder="Nguyễn Văn A&#10;Trần Văn B&#10;Lê Văn C"></textarea>
                                </div>

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
        @elseif ($format === 'stepladder_top5')
            <!-- Stepladder Top 5 Setup -->
            <div class="space-y-6">
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div>
                        <h2 class="text-base font-bold text-white flex items-center gap-2">
                            <span>🏅</span> Danh Sách 5 Kỳ Thủ Xếp Hạng Vòng Bảng
                        </h2>
                        <p class="text-xs text-slate-400 mt-1">
                            Nhập tên 5 kỳ thủ theo thứ tự thứ hạng từ Hạng 1 đến Hạng 5:
                        </p>
                    </div>

                    <div class="space-y-3 max-w-xl">
                        <div class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-amber-400 px-2.5 py-1.5 rounded-lg bg-amber-500/10 border border-amber-500/30 text-center">
                                Hạng 1 (👑)
                            </span>
                            <input type="text" wire:model="stepladder_players.0" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Kỳ thủ Hạng 1 (Chờ sẵn ở Chung kết)">
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-slate-200 px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-center">
                                Hạng 2 (Trận 1)
                            </span>
                            <input type="text" wire:model="stepladder_players.1" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Kỳ thủ Hạng 2 (Gặp Hạng 3)">
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-slate-200 px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-center">
                                Hạng 3 (Trận 1)
                            </span>
                            <input type="text" wire:model="stepladder_players.2" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Kỳ thủ Hạng 3 (Gặp Hạng 2)">
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-amber-600 px-2.5 py-1.5 rounded-lg bg-amber-700/10 border border-amber-700/20 text-center">
                                Hạng 4 (Trận 2)
                            </span>
                            <input type="text" wire:model="stepladder_players.3" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Kỳ thủ Hạng 4 (Gặp Hạng 5)">
                        </div>

                        <div class="flex items-center gap-3">
                            <span class="w-24 text-xs font-bold text-slate-400 px-2.5 py-1.5 rounded-lg bg-slate-800 border border-slate-700 text-center">
                                Hạng 5 (Trận 2)
                            </span>
                            <input type="text" wire:model="stepladder_players.4" class="flex-1 bg-slate-950 border border-slate-700 rounded-lg px-3.5 py-2 text-sm text-white focus:outline-none focus:border-amber-500" placeholder="Kỳ thủ Hạng 5 (Gặp Hạng 4)">
                        </div>
                    </div>
                </div>

                <!-- Time controls for Stepladder -->
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div>
                            <h3 class="text-sm font-semibold text-white">Cấu Hình Số Ván & Thời Gian Mỗi Trận Đấu</h3>
                            <p class="text-xs text-slate-400">Thiết lập số ván thi đấu (ví dụ 3 ván) và thời gian kiểm soát cho mỗi ván.</p>
                        </div>
                        <button type="button" wire:click="addSwissGame" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                            + Thêm Ván
                        </button>
                    </div>

                    <div class="space-y-2.5">
                        @foreach ($swiss_games as $gIdx => $g)
                            <div class="flex flex-wrap items-center justify-between gap-4 p-3 bg-slate-950 border border-slate-800 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-500/10 text-amber-400 font-bold text-xs flex items-center justify-center border border-amber-500/20">
                                        {{ $g['game_number'] }}
                                    </span>
                                    <span class="font-medium text-sm text-white">Ván {{ $g['game_number'] }}</span>
                                </div>

                                <div class="flex items-center gap-4">
                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-400">Gốc:</label>
                                        <input type="number" min="1" max="180" wire:model="swiss_games.{{ $gIdx }}.base_minutes" class="w-16 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                        <span class="text-slate-500 text-xs">phút</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-400">+</label>
                                        <input type="number" min="0" max="60" wire:model="swiss_games.{{ $gIdx }}.increment_seconds" class="w-16 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                        <span class="text-slate-500 text-xs">giây</span>
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
                                        <input type="number" min="1" max="180" wire:model="swiss_games.{{ $gIdx }}.base_minutes" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                        <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">phút</span>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <label class="text-xs text-slate-400">+</label>
                                        <input type="number" min="0" max="60" wire:model="swiss_games.{{ $gIdx }}.increment_seconds" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-amber-500 font-mono">
                                        <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">giây</span>
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
            <button type="submit" wire:loading.attr="disabled" class="w-full py-4 px-6 rounded-xl font-bold text-base bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-lg shadow-amber-500/10 flex items-center justify-center gap-3 transition cursor-pointer">
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
