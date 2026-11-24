<div class="bg-slate-900 border {{ $match->status === 'completed' ? 'border-emerald-500/40' : 'border-slate-800' }} rounded-xl overflow-hidden transition shadow-sm">
    <!-- Match Card Header -->
    <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60">
        <!-- Players & Score -->
        <div class="flex-1 flex flex-wrap md:flex-nowrap items-center justify-between md:justify-start gap-4">
            @if ($showContext && $match->round)
                <span class="text-xs px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 font-bold border border-amber-500/20 whitespace-nowrap">
                    {{ $match->round->stage_name ?: ($match->round->group ? $match->round->group->name . ' • ' : '') . 'Vòng ' . $match->round->round_number }}
                </span>
            @endif

            <!-- Player 1 -->
            <div class="flex items-center gap-2 flex-1 md:flex-none md:w-48 text-right md:text-left">
                <span class="font-bold text-base {{ $match->winner_id === $match->player1_id ? 'text-amber-400 font-extrabold' : 'text-white' }} truncate">
                    {{ $match->player1->name }}
                    @if ($match->winner_id === $match->player1_id && $match->isTied())
                        <span class="text-xs text-amber-400 ml-1" title="Thắng theo ưu thế xếp hạng">👑</span>
                    @endif
                </span>
            </div>

            <!-- Score Badge -->
            <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 font-mono font-extrabold text-base {{ $match->status === 'completed' ? 'text-emerald-400 border-emerald-500/30' : 'text-amber-400' }}">
                <span>{{ number_format($match->player1_score, 1) }}</span>
                <span class="text-slate-600">:</span>
                <span>{{ number_format($match->player2_score, 1) }}</span>
            </div>

            <!-- Player 2 -->
            <div class="flex items-center gap-2 flex-1 md:flex-none md:w-48">
                @if ($match->is_bye)
                    <span class="text-sm italic text-slate-500">(Nghỉ đấu / Bye)</span>
                @else
                    <span class="font-bold text-base {{ $match->winner_id === $match->player2_id ? 'text-amber-400 font-extrabold' : 'text-white' }} truncate">
                        {{ $match->player2?->name }}
                        @if ($match->winner_id === $match->player2_id && $match->isTied())
                            <span class="text-xs text-amber-400 ml-1" title="Thắng theo ưu thế xếp hạng">👑</span>
                        @endif
                    </span>
                @endif
            </div>
        </div>

        <!-- Controls: Accordion Toggle & Confirm Button -->
        <div class="flex flex-wrap items-center gap-2.5 justify-end border-t md:border-t-0 pt-3 md:pt-0 border-slate-800/80">
            @if ($match->is_bye)
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                    Miễn đấu (+1.0)
                </span>
            @else
                <!-- Admin Tie-break Selection Buttons -->
                @if ($isAdmin && $match->status !== 'completed' && $match->areAllGamesFinished() && $match->isTied())
                    <div class="flex flex-wrap items-center gap-1.5 p-1 rounded-lg bg-amber-500/10 border border-amber-500/30 text-xs">
                        <span class="text-amber-300 font-medium pl-1 hidden sm:inline">Hòa! Chọn thắng (BXH):</span>
                        <button type="button" wire:click="selectWinner({{ $match->player1_id }})" class="px-2 py-0.5 rounded font-bold transition cursor-pointer {{ $match->winner_id === $match->player1_id ? 'bg-amber-500 text-slate-950 shadow' : 'bg-slate-800 text-amber-300 hover:bg-slate-700' }}" title="Chọn {{ $match->player1->name }} thắng theo ưu thế xếp hạng">
                            👑 {{ $match->player1->name }}
                        </button>
                        <button type="button" wire:click="selectWinner({{ $match->player2_id }})" class="px-2 py-0.5 rounded font-bold transition cursor-pointer {{ $match->winner_id === $match->player2_id ? 'bg-amber-500 text-slate-950 shadow' : 'bg-slate-800 text-amber-300 hover:bg-slate-700' }}" title="Chọn {{ $match->player2->name }} thắng theo ưu thế xếp hạng">
                            👑 {{ $match->player2->name }}
                        </button>
                        <button type="button" wire:click="addTieBreakGame" class="px-2 py-0.5 rounded bg-slate-800 text-slate-200 hover:bg-slate-700 font-semibold transition cursor-pointer" title="Đấu thêm ván phụ nếu điểm xếp hạng vẫn bằng nhau">
                            ⚡ + Tie-break
                        </button>
                    </div>
                @endif

                <!-- Accordion Toggle Button -->
                <button type="button" wire:click="toggleOpen" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition cursor-pointer">
                    <span>{{ $match->games->count() }} ván</span>
                    <span class="transform transition-transform duration-200 {{ $isOpen ? 'rotate-180' : '' }}">▼</span>
                </button>

                <!-- Status & Confirmation Actions -->
                @if ($isAdmin)
                    @if ($match->status === 'completed')
                        <div class="flex items-center gap-2">
                            <span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                                <span>✓</span> Đã chốt điểm
                            </span>
                            <button type="button" wire:click="unlockMatch" class="text-xs text-slate-400 hover:text-amber-400 underline cursor-pointer" title="Mở khóa để sửa điểm">
                                Sửa lại
                            </button>
                        </div>
                    @else
                        @if ($match->areAllGamesFinished())
                            @if ($match->round?->isKnockout() && $match->isTied() && ! $match->winner_id)
                                <button type="button" disabled class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-amber-600/30 text-amber-300 border border-amber-500/30 cursor-not-allowed" title="Trận hòa: Hãy chọn người thắng theo BXH hoặc đấu Tie-break!">
                                    <span>⚠️</span> Cần chọn người thắng
                                </button>
                            @else
                                <button type="button" wire:click="confirmMatch" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-950 transition cursor-pointer">
                                    <span>✓</span> Xác nhận cặp này
                                </button>
                            @endif
                        @else
                            <button type="button" disabled class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-slate-800/60 text-slate-500 border border-slate-800 cursor-not-allowed" title="Hãy ghi điểm tất cả các ván trước">
                                <span>⏳</span> Chưa xong điểm
                            </button>
                        @endif
                    @endif
                @else
                    @if ($match->status === 'completed')
                        <span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            <span>✓</span> Đã hoàn tất
                        </span>
                    @else
                        <span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-800 text-slate-400">
                            <span>⏳</span> Đang diễn ra
                        </span>
                    @endif
                @endif
            @endif
        </div>
    </div>

    <!-- Error notice if any -->
    @if (session()->has('match_error'))
        <div class="px-4 py-2 bg-rose-500/10 border-t border-rose-500/20 text-rose-400 text-xs font-medium">
            ⚠️ {{ session('match_error') }}
        </div>
    @endif

    <!-- Accordion Dropdown Body (Games List) -->
    @if ($isOpen && ! $match->is_bye)
        <div class="border-t border-slate-800/80 bg-slate-950/60 p-4 space-y-3">
            <div class="flex items-center justify-between text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                <span>{{ $isAdmin ? 'Chi tiết từng ván đấu (Nhấn kết quả để ghi điểm):' : 'Kết quả chi tiết từng ván đấu:' }}</span>
                @if ($isAdmin && $match->status !== 'completed')
                    <button type="button" wire:click="addTieBreakGame" class="text-amber-400 hover:text-amber-300 normal-case flex items-center gap-1 cursor-pointer">
                        <span>⚡</span> + Thêm ván phụ (Tie-break)
                    </button>
                @endif
            </div>

            @foreach ($match->games as $game)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-lg bg-slate-900 border {{ $game->is_tie_break ? 'border-amber-500/40 bg-amber-500/5' : 'border-slate-800' }}">
                    <!-- Game Info & Colors -->
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-xs font-bold {{ $game->is_tie_break ? 'bg-amber-500 text-slate-950' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20' }}">
                            {{ $game->is_tie_break ? 'Tie-break ' . $game->game_number : 'Ván ' . $game->game_number }}
                        </span>

                        <span class="text-xs px-2 py-0.5 rounded bg-slate-800 text-slate-300 font-mono">
                            ⏱ {{ $game->time_control_label }}
                        </span>

                        <div class="text-sm flex items-center gap-2">
                            <span class="inline-flex items-center gap-1 font-medium text-slate-200">
                                <span class="w-3 h-3 rounded-full bg-white border border-slate-400 inline-block"></span>
                                {{ $game->whitePlayer->name }}
                            </span>
                            <span class="text-slate-600 font-bold">vs</span>
                            <span class="inline-flex items-center gap-1 font-medium text-slate-200">
                                <span class="w-3 h-3 rounded-full bg-slate-900 border border-slate-600 inline-block"></span>
                                {{ $game->blackPlayer->name }}
                            </span>
                        </div>
                    </div>

                    <!-- Result: Interactive Buttons for Admin, Static Badges for Viewers -->
                    @if ($isAdmin)
                        <div class="flex items-center gap-1.5">
                            <button type="button"
                                wire:click="setGameResult({{ $game->id }}, '1-0')"
                                @disabled($match->status === 'completed')
                                class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '1-0' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                                1 - 0
                            </button>

                            <button type="button"
                                wire:click="setGameResult({{ $game->id }}, '0.5-0.5')"
                                @disabled($match->status === 'completed')
                                class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '0.5-0.5' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                                ½ - ½
                            </button>

                            <button type="button"
                                wire:click="setGameResult({{ $game->id }}, '0-1')"
                                @disabled($match->status === 'completed')
                                class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '0-1' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                                0 - 1
                            </button>
                        </div>
                    @else
                        <div>
                            @if ($game->result)
                                <span class="px-3 py-1 rounded text-xs font-mono font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                    {{ $game->result === '0.5-0.5' ? '½ - ½' : $game->result }}
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded text-xs text-slate-500 bg-slate-900 border border-slate-800">
                                    Chưa đấu
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
