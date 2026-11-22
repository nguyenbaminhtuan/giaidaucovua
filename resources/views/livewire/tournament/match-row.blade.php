<div class="bg-slate-900 border {{ $match->status === 'completed' ? 'border-emerald-500/40' : 'border-slate-800' }} rounded-xl overflow-hidden transition shadow-sm">
    <!-- Match Card Header -->
    <div class="p-4 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-slate-900/60">
        <!-- Players & Score -->
        <div class="flex-1 flex flex-wrap md:flex-nowrap items-center justify-between md:justify-start gap-4">
            @if ($showContext && $match->round)
                <span class="text-xs px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-400 font-bold border border-amber-500/20 whitespace-nowrap">
                    {{ $match->round->group ? $match->round->group->name . ' • ' : '' }}Vòng {{ $match->round->round_number }}
                </span>
            @endif

            <!-- Player 1 -->
            <div class="flex items-center gap-2 flex-1 md:flex-none md:w-48 text-right md:text-left">
                <span class="font-bold text-base text-white truncate">{{ $match->player1->name }}</span>
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
                    <span class="font-bold text-base text-white truncate">{{ $match->player2?->name }}</span>
                @endif
            </div>
        </div>

        <!-- Controls: Accordion Toggle & Confirm Button -->
        <div class="flex items-center gap-3 justify-end border-t md:border-t-0 pt-3 md:pt-0 border-slate-800/80">
            @if ($match->is_bye)
                <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-slate-800 text-slate-400 border border-slate-700">
                    Miễn đấu (+1.0)
                </span>
            @else
                <!-- Accordion Toggle Button -->
                <button type="button" wire:click="toggleOpen" class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition">
                    <span>{{ $match->games->count() }} ván đấu</span>
                    <span class="transform transition-transform duration-200 {{ $isOpen ? 'rotate-180' : '' }}">▼</span>
                </button>

                <!-- Green Checkmark Confirmation Button -->
                @if ($match->status === 'completed')
                    <div class="flex items-center gap-2">
                        <span class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                            <span>✓</span> Đã chốt điểm
                        </span>
                        <button type="button" wire:click="unlockMatch" class="text-xs text-slate-400 hover:text-amber-400 underline" title="Mở khóa để sửa điểm">
                            Sửa lại
                        </button>
                    </div>
                @else
                    @if ($match->areAllGamesFinished())
                        <button type="button" wire:click="confirmMatch" wire:loading.attr="disabled" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-950 transition cursor-pointer">
                            <span>✓</span> Xác nhận cặp này
                        </button>
                    @else
                        <button type="button" disabled class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-medium bg-slate-800/60 text-slate-500 border border-slate-800 cursor-not-allowed" title="Hãy ghi điểm tất cả các ván trước">
                            <span>⏳</span> Chưa xong điểm
                        </button>
                    @endif
                @endif
            @endif
        </div>
    </div>

    <!-- Accordion Dropdown Body (Games List) -->
    @if ($isOpen && ! $match->is_bye)
        <div class="border-t border-slate-800/80 bg-slate-950/60 p-4 space-y-3">
            <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">
                Chi tiết từng ván đấu (Nhấn kết quả để ghi điểm):
            </div>

            @foreach ($match->games as $game)
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-lg bg-slate-900 border border-slate-800">
                    <!-- Game Info & Colors -->
                    <div class="flex items-center gap-3">
                        <span class="px-2 py-0.5 rounded text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            Ván {{ $game->game_number }}
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

                    <!-- 3 Rapid Result Buttons -->
                    <div class="flex items-center gap-1.5">
                        <!-- White Wins (1-0) -->
                        <button type="button"
                            wire:click="setGameResult({{ $game->id }}, '1-0')"
                            @disabled($match->status === 'completed')
                            class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '1-0' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                            1 - 0
                        </button>

                        <!-- Draw (0.5 - 0.5) -->
                        <button type="button"
                            wire:click="setGameResult({{ $game->id }}, '0.5-0.5')"
                            @disabled($match->status === 'completed')
                            class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '0.5-0.5' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                            ½ - ½
                        </button>

                        <!-- Black Wins (0-1) -->
                        <button type="button"
                            wire:click="setGameResult({{ $game->id }}, '0-1')"
                            @disabled($match->status === 'completed')
                            class="px-2.5 py-1 text-xs font-bold rounded border transition {{ $game->result === '0-1' ? 'bg-amber-500 text-slate-950 border-amber-400' : 'bg-slate-800 hover:bg-slate-700 text-slate-300 border-slate-700' }}">
                            0 - 1
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
