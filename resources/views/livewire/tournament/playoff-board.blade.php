<div class="space-y-8">
    <!-- Playoff Header & Notification -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20 uppercase tracking-wider">
                    Knockout Stage
                </span>
            </div>
            <h2 class="text-2xl font-black text-white">
                🥊 Vòng Loại Trực Tiếp (Playoff)
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Các kỳ thủ đầu bảng gặp nhau theo thể thức đấu loại trực tiếp để tranh Cúp Vô Địch và Hạng Ba.
            </p>
        </div>

        @if ($knockoutRounds->isNotEmpty())
            <button type="button"
                    wire:click="resetKnockoutStage"
                    wire:confirm="Bạn có chắc chắn muốn xóa và làm lại toàn bộ nhánh đấu Knockout không?"
                    class="text-xs text-slate-500 hover:text-rose-400 border border-slate-800 hover:border-rose-500/30 px-3 py-1.5 rounded-lg transition">
                Xóa làm lại nhánh đấu
            </button>
        @endif
    </div>

    <!-- Feedback alerts -->
    @if (session()->has('playoff_msg'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-medium flex items-center gap-2">
            <span>✓</span> {{ session('playoff_msg') }}
        </div>
    @endif

    @if (session()->has('playoff_error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-medium flex items-center gap-2">
            <span>⚠️</span> {{ session('playoff_error') }}
        </div>
    @endif

    @if ($knockoutRounds->isEmpty())
        <!-- Stage 0: Initial Configuration & Preview of advancing players -->
        <div class="space-y-6">
            <!-- Qualifying Players Preview -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>👥</span> Danh Sách Kỳ Thủ Đạt Chuẩn Đi Tiếp Vào Vòng Loại Trực Tiếp
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Dựa theo thứ hạng trên bảng xếp hạng vòng bảng hiện tại.</p>
                    </div>

                    <!-- Change advancing count on the fly -->
                    <div class="flex items-center gap-2 text-xs">
                        <span class="text-slate-400">Chọn số người đi tiếp:</span>
                        <select wire:change="updateAdvancingCount($event.target.value)" class="bg-slate-950 border border-slate-700 rounded-lg px-2.5 py-1.5 text-amber-400 font-bold focus:outline-none focus:border-amber-500">
                            <option value="4" @selected($advancingCount == 4)>Top 4 mỗi bảng (Vào Tứ Kết - 8 người)</option>
                            <option value="2" @selected($advancingCount == 2)>Top 2 mỗi bảng (Vào Bán Kết - 4 người)</option>
                            <option value="1" @selected($advancingCount == 1)>Top 1 mỗi bảng (Vào Chung Kết - 2 người)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($advancingPreview as $groupName => $players)
                        <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 space-y-2.5">
                            <span class="font-bold text-sm text-amber-400 block border-b border-slate-800 pb-2">
                                {{ $groupName }} (Lấy Top {{ $advancingCount }})
                            </span>

                            @forelse ($players as $idx => $st)
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded bg-slate-900/60">
                                    <span class="font-medium text-white flex items-center gap-2">
                                        <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-300 flex items-center justify-center font-bold text-xs">
                                            {{ $idx + 1 }}
                                        </span>
                                        {{ $st->participant->name }}
                                    </span>
                                    <span class="font-mono text-amber-400 font-bold">
                                        {{ number_format($st->points, 1) }} đ
                                    </span>
                                </div>
                            @empty
                                <span class="text-xs text-slate-500">Chưa có kết quả thi đấu.</span>
                            @endforelse
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Time Controls Config for Knockout Matches -->
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-800 pb-4">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            <span>⏱</span> Thiết Lập Số Ván & Thời Gian Vòng Loại Trực Tiếp
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">Mỗi cặp đấu Knockout sẽ thi đấu theo cấu hình số ván này.</p>
                    </div>
                    <button type="button" wire:click="addKnockoutGame" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                        + Thêm Ván
                    </button>
                </div>

                <div class="space-y-3">
                    @foreach ($knockoutGames as $gIdx => $g)
                        <div class="flex flex-wrap items-center justify-between gap-4 p-3 bg-slate-950 border border-slate-800 rounded-xl">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-rose-500/10 text-rose-400 font-bold text-xs flex items-center justify-center border border-rose-500/20">
                                    {{ $g['game_number'] }}
                                </span>
                                <span class="font-bold text-sm text-white">Ván {{ $g['game_number'] }}</span>
                            </div>

                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400">Thời gian gốc:</span>
                                    <div class="flex items-center">
                                        <input type="number" min="1" max="180" wire:model="knockoutGames.{{ $gIdx }}.base_minutes" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-rose-500">
                                        <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">phút</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-slate-400">Gia tăng (+):</span>
                                    <div class="flex items-center">
                                        <input type="number" min="0" max="60" wire:model="knockoutGames.{{ $gIdx }}.increment_seconds" class="w-16 bg-slate-900 border border-slate-700 rounded-l px-2 py-1 text-sm text-center text-white focus:outline-none focus:border-rose-500">
                                        <span class="bg-slate-800 text-slate-400 px-2 py-1 text-xs border border-l-0 border-slate-700 rounded-r">giây</span>
                                    </div>
                                </div>

                                @if (count($knockoutGames) > 1)
                                    <button type="button" wire:click="removeKnockoutGame({{ $gIdx }})" class="text-xs text-rose-400 hover:text-rose-300">
                                        Xóa
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Start Playoff Button -->
            <button type="button"
                    wire:click="startKnockoutStage"
                    class="w-full py-4 px-6 rounded-2xl font-black text-base bg-gradient-to-r from-rose-600 to-amber-600 hover:from-rose-500 hover:to-amber-500 text-white shadow-xl shadow-rose-950 flex items-center justify-center gap-2 transition cursor-pointer">
                <span>🔥</span>
                <span>Khởi Tạo Vòng Loại Trực Tiếp (1A vs 4B, 1B vs 4A, 2B vs 3A, 2A vs 3B)</span>
            </button>
        </div>
    @else
        <!-- Knockout Rounds Display (Tree / Rounds) -->
        <div class="space-y-10">
            @foreach ($knockoutRounds as $rIndex => $round)
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-3">
                            <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-gradient-to-r from-rose-600 to-amber-600 text-white shadow">
                                {{ $round->stage_name }}
                            </span>
                            <span class="text-xs text-slate-400">
                                ({{ $round->matches->count() }} cặp đấu)
                            </span>
                        </div>

                        <!-- Check if this round is fully completed -->
                        @php
                            $allDone = $round->matches->every(fn($m) => $m->status === 'completed' && ! $m->isTied());
                            $isLastKnockoutRound = ($rIndex === $knockoutRounds->count() - 1);
                        @endphp

                        @if ($allDone && $isLastKnockoutRound)
                            @if ($round->stage_name === 'Tứ Kết')
                                <button type="button"
                                        wire:click="generateNextRound({{ $round->id }})"
                                        class="px-4 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/20 transition cursor-pointer flex items-center gap-1.5">
                                    <span>➔</span> Bốc Thăm Vòng Bán Kết
                                </button>
                            @elseif ($round->stage_name === 'Bán Kết')
                                <button type="button"
                                        wire:click="generateNextRound({{ $round->id }})"
                                        class="px-4 py-2 rounded-xl text-xs font-bold bg-emerald-500 hover:bg-emerald-400 text-slate-950 shadow-md shadow-emerald-500/20 transition cursor-pointer flex items-center gap-1.5">
                                    <span>🏆</span> Bốc Thăm Chung Kết & Tranh Hạng 3
                                </button>
                            @elseif (str_contains($round->stage_name, 'Chung Kết'))
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500/20 text-amber-400 border border-amber-500/30">
                                    👑 Giải Đấu Đã Hoàn Thành!
                                </span>
                            @endif
                        @endif
                    </div>

                    <!-- Match Rows inside this Knockout Round -->
                    <div class="space-y-3">
                        @foreach ($round->matches as $mIdx => $match)
                            <div class="relative">
                                @if (str_contains($round->stage_name, 'Chung Kết'))
                                    <div class="mb-1 text-xs font-bold {{ $mIdx === 0 ? 'text-amber-400' : 'text-slate-400' }}">
                                        {{ $mIdx === 0 ? '👑 TRẬN CHUNG KẾT (TRANH NGÔI VÔ ĐỊCH)' : '🥉 TRẬN TRANH HẠNG BA' }}
                                    </div>
                                @endif
                                <livewire:tournament.match-row :matchId="$match->id" :key="'ko-row-' . $match->id" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
