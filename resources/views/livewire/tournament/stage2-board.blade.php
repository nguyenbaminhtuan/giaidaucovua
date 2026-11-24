<div class="space-y-6">
    <!-- Header -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20 uppercase tracking-wider">
                    Giai Đoạn 2 (Stage 2)
                </span>
            </div>
            <h2 class="text-2xl font-black text-white">
                🔄 Vòng Tròn 8 Người & Điểm Thưởng Knockout
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                8 kỳ thủ đầu bảng thi đấu vòng tròn 7 vòng kết hợp điểm thưởng từ nhánh Playoff để chọn ra Top 5 vào Chung Kết.
            </p>
        </div>

        <!-- Sub Tabs -->
        <div class="flex items-center gap-1.5 p-1 bg-slate-950 border border-slate-800 rounded-xl">
            <button type="button" wire:click="switchSubTab('round_robin')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $activeSubTab === 'round_robin' ? 'bg-cyan-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                ⚔️ 7 Vòng Đấu
            </button>
            <button type="button" wire:click="switchSubTab('combined_standings')" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $activeSubTab === 'combined_standings' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                📊 BXH Tổng Hợp (Kèm Thưởng)
            </button>
        </div>
    </div>

    <!-- Feedback messages -->
    @if (session()->has('stage2_msg'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-medium flex items-center gap-2">
            <span>✓</span> {{ session('stage2_msg') }}
        </div>
    @endif

    @if (session()->has('stage2_error'))
        <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm font-medium flex items-center gap-2">
            <span>⚠️</span> {{ session('stage2_error') }}
        </div>
    @endif

    @if ($activeSubTab === 'round_robin')
        <!-- Round Robin 7 Rounds View -->
        @if ($rounds->isEmpty())
            <div class="space-y-6">
                <!-- Bonus Points Config -->
                @if ($isAdmin)
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <div class="border-b border-slate-800 pb-3">
                            <h3 class="text-base font-bold text-white flex items-center gap-2">
                                <span>🎁</span> Cấu Hình Mức Điểm Thưởng Từ Nhánh Knockout
                            </h3>
                            <p class="text-xs text-slate-400 mt-0.5">Điểm này sẽ được tự động cộng dồn vào BXH vòng tròn 8 người.</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl space-y-1">
                                <span class="text-xs text-amber-400 font-bold block">Top 1 Nhánh Playoff:</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.5" wire:model="bonusTop1" class="w-20 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white font-bold text-center">
                                    <span class="text-xs text-slate-400">điểm</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl space-y-1">
                                <span class="text-xs text-slate-300 font-bold block">Top 2 Nhánh Playoff:</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.5" wire:model="bonusTop2" class="w-20 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white font-bold text-center">
                                    <span class="text-xs text-slate-400">điểm</span>
                                </div>
                            </div>

                            <div class="p-3 bg-slate-950 border border-slate-800 rounded-xl space-y-1">
                                <span class="text-xs text-amber-600 font-bold block">Top 3 Nhánh Playoff:</span>
                                <div class="flex items-center gap-2">
                                    <input type="number" step="0.5" wire:model="bonusTop3" class="w-20 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-white font-bold text-center">
                                    <span class="text-xs text-slate-400">điểm</span>
                                </div>
                            </div>
                        </div>

                        <button type="button" wire:click="saveBonusConfig" class="text-xs px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-amber-400 font-bold rounded-lg border border-slate-700 transition">
                            Lưu mức điểm thưởng
                        </button>
                    </div>

                    <!-- Time control config -->
                    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2">
                                    <span>⏱</span> Số Ván & Thời Gian Thi Đấu Vòng Tròn 8 Người
                                </h3>
                                <p class="text-xs text-slate-400 mt-0.5">Mỗi cặp đấu vòng tròn sẽ thi đấu theo cấu hình này.</p>
                            </div>
                            <button type="button" wire:click="addGame" class="px-3 py-1.5 text-xs font-bold rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                                + Thêm Ván
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            @foreach ($timeControls as $gIdx => $g)
                                <div class="flex flex-wrap items-center justify-between gap-4 p-3 bg-slate-950 border border-slate-800 rounded-xl">
                                    <span class="font-bold text-sm text-white">Ván {{ $g['game_number'] }}</span>
                                    <div class="flex items-center gap-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-400">Gốc:</span>
                                            <input type="number" min="1" max="180" wire:model="timeControls.{{ $gIdx }}.base_minutes" class="w-16 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-sm text-center text-white font-mono">
                                            <span class="text-slate-500 text-xs">phút</span>
                                        </div>
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs text-slate-400">+</span>
                                            <input type="number" min="0" max="60" wire:model="timeControls.{{ $gIdx }}.increment_seconds" class="w-16 bg-slate-900 border border-slate-700 rounded px-2 py-1 text-sm text-center text-white font-mono">
                                            <span class="text-slate-500 text-xs">giây</span>
                                        </div>
                                        @if (count($timeControls) > 1)
                                            <button type="button" wire:click="removeGame({{ $gIdx }})" class="text-xs text-rose-400 hover:text-rose-300">
                                                Xóa
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="button"
                            wire:click="generateStage2Rounds"
                            class="w-full py-4 px-6 rounded-2xl font-black text-base bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white shadow-xl shadow-cyan-950 flex items-center justify-center gap-2 transition cursor-pointer">
                        <span>🔄</span>
                        <span>Khởi Tạo 7 Vòng Đấu Cho 8 Kỳ Thủ Đi Tiếp</span>
                    </button>
                @else
                    <div class="text-center py-12 bg-slate-900 border border-slate-800 rounded-2xl text-slate-400 text-sm">
                        ℹ️ Chờ Ban tổ chức khởi tạo 7 vòng đấu vòng tròn Giai Đoạn 2.
                    </div>
                @endif
            </div>
        @else
            <!-- Display 7 Rounds -->
            <div class="space-y-4">
                <!-- Round Selector Pills -->
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-2 overflow-x-auto">
                    <span class="text-xs font-semibold text-slate-400 uppercase mr-1">Vòng đấu:</span>
                    @foreach ($rounds as $r)
                        <button type="button"
                                wire:click="selectRound({{ $r->round_number }})"
                                class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ ($activeRound?->round_number ?? 1) === $r->round_number ? 'bg-cyan-500 text-slate-950 shadow' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                            Vòng {{ $r->round_number }}
                        </button>
                    @endforeach
                </div>

                <!-- Matches in Selected Round -->
                @if ($matches->isEmpty())
                    <div class="text-center py-8 bg-slate-900 border border-slate-800 rounded-xl text-slate-400 text-sm">
                        Chưa có cặp đấu trong vòng này.
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach ($matches as $match)
                            <livewire:tournament.match-row :matchId="$match->id" :key="'st2-match-' . $match->id" />
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @else
        <!-- Combined Standings Table -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm space-y-4 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <span>📊</span> Bảng Xếp Hạng Tổng Hợp Giai Đoạn 2
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Tổng điểm = [Điểm đấu vòng tròn] + [Điểm thưởng từ Nhánh Playoff]. <strong>Top 5</strong> kỳ thủ cao nhất sẽ giành vé vào Vòng Chung Kết Stepladder!
                    </p>
                </div>

                @if ($isAdmin && $standings->count() >= 5)
                    <button type="button"
                            wire:click="promoteToStepladder"
                            class="px-4 py-2.5 rounded-xl text-xs font-black bg-gradient-to-r from-amber-500 to-rose-500 hover:from-amber-400 hover:to-rose-400 text-slate-950 shadow-lg shadow-amber-500/20 transition cursor-pointer flex items-center gap-1.5">
                        <span>👑</span> Chuyển Top 5 Vào Chung Kết Stepladder
                    </button>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-950 text-slate-400 text-xs uppercase font-semibold border-b border-slate-800">
                        <tr>
                            <th class="py-3 px-3 text-center w-14">Hạng</th>
                            <th class="py-3 px-4">Kỳ Thủ</th>
                            <th class="py-3 px-2 text-center w-16">Trận</th>
                            <th class="py-3 px-2 text-center w-12 text-emerald-400">T</th>
                            <th class="py-3 px-2 text-center w-12 text-slate-300">H</th>
                            <th class="py-3 px-2 text-center w-12 text-rose-400">B</th>
                            <th class="py-3 px-3 text-center w-24 text-slate-300 font-mono">Điểm Vòng Tròn</th>
                            <th class="py-3 px-3 text-center w-28 text-cyan-400 font-mono font-bold">🎁 Điểm Thưởng</th>
                            <th class="py-3 px-4 text-right w-28 text-amber-400 font-black">⭐ TỔNG ĐIỂM</th>
                            <th class="py-3 px-3 text-center w-28">Trạng Thái</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/80">
                        @forelse ($standings as $idx => $st)
                            @php
                                $rank = $idx + 1;
                                $isTop5 = $rank <= 5;
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition {{ $isTop5 ? 'bg-amber-500/5' : '' }}">
                                <td class="py-3 px-3 text-center font-bold">
                                    @if ($rank === 1)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-400 text-slate-950 font-black text-xs">1</span>
                                    @elseif ($rank === 2)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-300 text-slate-950 font-black text-xs">2</span>
                                    @elseif ($rank === 3)
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-amber-700 text-white font-black text-xs">3</span>
                                    @else
                                        <span class="text-xs font-mono text-slate-400">{{ $rank }}</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 font-semibold text-white">
                                    {{ $st->participant->name }}
                                </td>

                                <td class="py-3 px-2 text-center font-mono text-slate-300">{{ $st->played }}</td>
                                <td class="py-3 px-2 text-center font-mono text-emerald-400">{{ $st->won }}</td>
                                <td class="py-3 px-2 text-center font-mono text-slate-400">{{ $st->drawn }}</td>
                                <td class="py-3 px-2 text-center font-mono text-rose-400">{{ $st->lost }}</td>

                                <td class="py-3 px-3 text-center font-mono text-slate-200">
                                    {{ number_format($st->points, 1) }}
                                </td>

                                <td class="py-3 px-3 text-center font-mono font-bold text-cyan-400">
                                    +{{ number_format($st->bonus_points, 1) }}
                                </td>

                                <td class="py-3 px-4 text-right font-mono font-black text-base text-amber-400">
                                    {{ number_format($st->total_points, 1) }}
                                </td>

                                <td class="py-3 px-3 text-center">
                                    @if ($isTop5)
                                        <span class="px-2 py-0.5 rounded-full text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30 whitespace-nowrap">
                                            ✓ Vào Chung Kết
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-500">Dừng bước</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-8 text-slate-500 text-xs">
                                    Chưa có dữ liệu xếp hạng Giai Đoạn 2. Hãy khởi tạo 7 vòng đấu trước.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>
