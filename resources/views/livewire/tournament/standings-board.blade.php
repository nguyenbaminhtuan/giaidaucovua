<div class="space-y-6">
    <!-- Group Tabs (if Round Robin) -->
    @if ($tournament->format === 'round_robin_groups' && $tournament->groups->isNotEmpty())
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex items-center gap-2 overflow-x-auto">
            <span class="text-xs font-semibold text-slate-400 uppercase mr-2">Bảng xếp hạng:</span>
            @foreach ($tournament->groups as $group)
                <button type="button" wire:click="selectGroup({{ $group->id }})" class="px-4 py-2 rounded-lg text-xs font-bold transition {{ $selectedGroupId === $group->id ? 'bg-amber-500 text-slate-950 shadow' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                    {{ $group->name }}
                </button>
            @endforeach
        </div>
    @endif

    <!-- Standings Table Card -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden shadow-sm">
        <div class="p-5 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span class="text-xl">🏆</span>
                <h2 class="text-lg font-bold text-white">
                    Bảng Xếp Hạng {{ $tournament->format === 'round_robin_groups' ? ($tournament->groups->firstWhere('id', $selectedGroupId)?->name ?? '') : 'Toàn Giải' }}
                </h2>
            </div>
            <span class="text-xs text-slate-400">
                Thắng: 1.0 • Hòa: 0.5 • Thua: 0.0
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-slate-950 text-slate-400 text-xs uppercase font-semibold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4 text-center w-16">Hạng</th>
                        <th class="py-3.5 px-4">Kỳ Thủ</th>
                        <th class="py-3.5 px-3 text-center w-20">Trận</th>
                        <th class="py-3.5 px-3 text-center w-16 text-emerald-400">T</th>
                        <th class="py-3.5 px-3 text-center w-16 text-slate-300">H</th>
                        <th class="py-3.5 px-3 text-center w-16 text-rose-400">B</th>
                        <th class="py-3.5 px-4 text-center w-28" title="{{ $tournament->format === 'round_robin_groups' ? 'Hệ số Sonneborn-Berger' : 'Hệ số Buchholz' }}">
                            {{ $tournament->format === 'round_robin_groups' ? 'Hệ Số SB' : 'Buchholz' }}
                        </th>
                        <th class="py-3.5 px-5 text-right w-24 text-amber-400 font-extrabold">Điểm</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse ($rows as $index => $row)
                        @php
                            $rank = $index + 1;
                        @endphp
                        <tr class="hover:bg-slate-800/40 transition {{ $rank === 1 ? 'bg-amber-500/5' : '' }}">
                            <!-- Rank -->
                            <td class="py-3.5 px-4 text-center font-bold">
                                @if ($rank === 1)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/20">1</span>
                                @elseif ($rank === 2)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-300 text-slate-950 font-black text-xs shadow-md shadow-slate-300/20">2</span>
                                @elseif ($rank === 3)
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-amber-700 text-white font-black text-xs shadow-md shadow-amber-700/20">3</span>
                                @else
                                    <span class="text-slate-500 text-xs font-mono font-medium">{{ $rank }}</span>
                                @endif
                            </td>

                            <!-- Player Name -->
                            <td class="py-3.5 px-4 font-semibold text-white">
                                <span class="hover:text-amber-400 transition">{{ $row->participant->name }}</span>
                            </td>

                            <!-- Played -->
                            <td class="py-3.5 px-3 text-center font-mono text-slate-300">{{ $row->played }}</td>

                            <!-- Won -->
                            <td class="py-3.5 px-3 text-center font-mono font-medium text-emerald-400">{{ $row->won }}</td>

                            <!-- Drawn -->
                            <td class="py-3.5 px-3 text-center font-mono text-slate-400">{{ $row->drawn }}</td>

                            <!-- Lost -->
                            <td class="py-3.5 px-3 text-center font-mono text-rose-400">{{ $row->lost }}</td>

                            <!-- Tie break -->
                            <td class="py-3.5 px-4 text-center font-mono text-xs text-slate-400">
                                {{ number_format($row->tie_break_score, 1) }}
                            </td>

                            <!-- Total Points -->
                            <td class="py-3.5 px-5 text-right font-mono font-black text-base text-amber-400">
                                {{ number_format($row->points, 1) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-8 text-slate-500 text-sm">
                                Chưa có dữ liệu kỳ thủ.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
