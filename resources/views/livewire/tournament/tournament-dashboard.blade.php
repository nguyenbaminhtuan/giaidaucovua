<div class="space-y-6">
    <!-- Tournament Header Banner -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider {{ $tournament->format === 'stepladder_top5' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($tournament->format === 'round_robin_groups' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20') }}">
                    {{ $tournament->format === 'stepladder_top5' ? 'Chung Kết Top 5 (Stepladder)' : ($tournament->format === 'round_robin_groups' ? 'Đấu Vòng Tròn Chia Bảng' : 'Hệ Thụy Sĩ (Swiss)') }}
                </span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                    Đang Diễn Ra
                </span>
            </div>

            @if ($isEditingName)
                <form wire:submit="saveName" class="flex flex-wrap items-center gap-2 mt-2">
                    <input type="text"
                           wire:model="editingName"
                           class="bg-slate-950 border border-amber-500 rounded-lg px-3 py-1.5 text-white text-lg font-bold focus:outline-none focus:ring-1 focus:ring-amber-500 w-full sm:w-80">
                    <button type="submit" class="px-3.5 py-1.5 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded-lg transition">
                        Lưu tên
                    </button>
                    <button type="button" wire:click="cancelEditingName" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold rounded-lg transition">
                        Hủy
                    </button>
                </form>
                @error('editingName') <span class="text-rose-400 text-xs block mt-1">{{ $message }}</span> @enderror
            @else
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-white tracking-tight">
                        {{ $tournament->name }}
                    </h1>
                    @if (session('is_admin'))
                        <button type="button"
                                wire:click="startEditingName"
                                class="text-xs px-2.5 py-1 rounded-md bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-amber-400 border border-slate-700 transition flex items-center gap-1 cursor-pointer"
                                title="Chỉnh sửa tên giải đấu">
                            <span>✏️</span>
                            <span>Đổi tên</span>
                        </button>
                    @endif
                </div>
            @endif

            @if (session()->has('name_updated'))
                <span class="text-emerald-400 text-xs block mt-1">✓ {{ session('name_updated') }}</span>
            @endif
            @if (session()->has('dashboard_msg'))
                <span class="text-emerald-400 text-xs block mt-1">✓ {{ session('dashboard_msg') }}</span>
            @endif
            @if (session()->has('dashboard_error'))
                <span class="text-rose-400 text-xs block mt-1">⚠️ {{ session('dashboard_error') }}</span>
            @endif
        </div>

        <!-- Quick Tabs -->
        <div class="flex flex-wrap items-center gap-1.5 p-1 bg-slate-950 border border-slate-800 rounded-xl">
            <button type="button" wire:click="switchTab('matches')" class="px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-bold transition {{ $activeTab === 'matches' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                ⚔️ Vòng Bảng (Giai Đoạn 1)
            </button>
            @if ($tournament->format === 'round_robin_groups')
                <button type="button" wire:click="switchTab('playoff')" class="px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-bold transition {{ $activeTab === 'playoff' ? 'bg-gradient-to-r from-rose-500 to-amber-500 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                    🥊 Nhánh Playoff (Điểm Thưởng)
                </button>
                <button type="button" wire:click="switchTab('stage2')" class="px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-bold transition {{ $activeTab === 'stage2' ? 'bg-cyan-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                    🔄 Giai Đoạn 2 (Vòng Tròn 8 Người)
                </button>
            @endif
            @if ($tournament->stepladderRounds()->exists() || $tournament->format === 'stepladder_top5')
                <button type="button" wire:click="switchTab('stepladder')" class="px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-bold transition {{ $activeTab === 'stepladder' ? 'bg-gradient-to-r from-amber-500 to-rose-500 text-white shadow' : 'text-slate-400 hover:text-white' }}">
                    👑 Chung Kết Stepladder
                </button>
            @endif
            <button type="button" wire:click="switchTab('standings')" class="px-3.5 py-1.5 rounded-lg text-xs md:text-sm font-bold transition {{ $activeTab === 'standings' ? 'bg-amber-500 text-slate-950 shadow' : 'text-slate-400 hover:text-white' }}">
                🏆 BXH Vòng Bảng
            </button>
        </div>
    </div>

    <!-- Visual Stage Pipeline Tracker for Multi-Stage Tournaments -->
    @if ($tournament->format === 'round_robin_groups')
        <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3 flex items-center justify-between">
                <span>Lộ trình các giai đoạn trong giải:</span>
                <span class="text-emerald-400 font-semibold lowercase">Tự động liên kết dữ liệu xuyên suốt</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- Stage 1 -->
                <div wire:click="switchTab('matches')" class="p-3.5 rounded-xl border transition cursor-pointer {{ $activeTab === 'matches' ? 'bg-amber-500/10 border-amber-500/50' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700' }}">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-amber-400">Giai Đoạn 1</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Vòng Bảng</span>
                    </div>
                    <div class="font-extrabold text-white text-sm">Vòng Bảng A & B</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Đấu vòng tròn chọn Top {{ $tournament->advancing_count ?: 4 }} mỗi bảng vào Giai đoạn 2.</p>
                </div>

                <!-- Stage 2 -->
                <div wire:click="switchTab('stage2')" class="p-3.5 rounded-xl border transition cursor-pointer {{ $activeTab === 'stage2' || $activeTab === 'playoff' ? 'bg-cyan-500/10 border-cyan-500/50' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700' }}">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-cyan-400">Giai Đoạn 2</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-cyan-500/10 text-cyan-400 border border-cyan-500/20">Đánh song song</span>
                    </div>
                    <div class="font-extrabold text-white text-sm">Playoff & Vòng Tròn 8 Người</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Playoff tranh điểm thưởng (+4, +3, +2) & Vòng tròn 7 vòng lấy Top 5.</p>
                </div>

                <!-- Stage 3 -->
                <div wire:click="switchTab('stepladder')" class="p-3.5 rounded-xl border transition cursor-pointer {{ $activeTab === 'stepladder' ? 'bg-rose-500/10 border-rose-500/50' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700' }}">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-rose-400">Giai Đoạn 3</span>
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20">Chung Kết</span>
                    </div>
                    <div class="font-extrabold text-white text-sm">Chung Kết Stepladder Top 5</div>
                    <p class="text-[11px] text-slate-400 mt-0.5">Trận 1, 2, Tranh HCĐ và Tranh HCV Vô Địch.</p>
                </div>
            </div>
        </div>
    @endif

    @if ($activeTab === 'matches')
        <!-- Matches Tab View -->
        <div class="space-y-4">
            <!-- Quick Search Match Box -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 flex items-center justify-between gap-3 shadow-sm">
                <div class="flex items-center gap-2.5 flex-1">
                    <span class="text-slate-500 text-sm">🔍</span>
                    <input type="text"
                           wire:model.live.debounce.250ms="matchSearch"
                           placeholder="Tìm kiếm nhanh cặp đấu theo tên kỳ thủ trong toàn giải..."
                           class="bg-transparent border-none text-white text-sm w-full focus:outline-none placeholder:text-slate-500">
                </div>

                @if ($isSearching)
                    <button type="button"
                            wire:click="clearMatchSearch"
                            class="text-xs px-2.5 py-1 rounded bg-slate-800 text-slate-300 hover:text-white flex items-center gap-1 transition">
                        <span>✕</span> Xóa tìm kiếm
                    </button>
                @endif
            </div>

            @if ($isSearching)
                <div class="flex items-center justify-between text-xs text-slate-400 px-1">
                    <span>Kết quả tìm kiếm cho: <strong class="text-amber-400">"{{ $matchSearch }}"</strong> ({{ $matches->count() }} cặp đấu)</span>
                </div>
            @else
                <!-- Filter Bar: Group Tabs (if Round Robin) & Round Selector -->
                <div class="bg-slate-900/70 border border-slate-800 rounded-xl p-4 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <!-- Group Tabs -->
                    @if ($tournament->format === 'round_robin_groups' && $tournament->groups->isNotEmpty())
                        <div class="flex items-center gap-2 overflow-x-auto pb-1 md:pb-0">
                            <span class="text-xs font-semibold text-slate-400 uppercase mr-1">Bảng:</span>
                            @foreach ($tournament->groups as $group)
                                <button type="button" wire:click="selectGroup({{ $group->id }})" class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition {{ $selectedGroupId === $group->id ? 'bg-amber-500 text-slate-950' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                                    {{ $group->name }}
                                </button>
                            @endforeach
                        </div>
                    @else
                        <div></div>
                    @endif

                    <!-- Round Selector -->
                    <div class="flex items-center gap-2 overflow-x-auto">
                        <span class="text-xs font-semibold text-slate-400 uppercase mr-1">Vòng/Giai đoạn:</span>
                        @foreach ($rounds as $r)
                            <button type="button" wire:click="selectRound({{ $r->round_number }})" class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($activeRound?->round_number ?? 1) === $r->round_number ? 'bg-slate-100 text-slate-950 shadow' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                                {{ $r->stage_name ?: 'Vòng ' . $r->round_number }}
                            </button>
                        @endforeach

                        @if ($tournament->format === 'swiss' && session('is_admin'))
                            <button type="button" wire:click="generateNextSwissRound" class="ml-2 px-3 py-1.5 rounded-lg text-xs font-bold bg-cyan-600 hover:bg-cyan-500 text-white transition cursor-pointer">
                                + Bốc Thăm Vòng Mới
                            </button>
                        @endif

                        @if ($tournament->format === 'stepladder_top5' && session('is_admin') && $rounds->count() < 2)
                            <button type="button" wire:click="generateStepladderFinals" class="ml-2 px-3 py-1.5 rounded-lg text-xs font-bold bg-gradient-to-r from-amber-500 to-rose-500 hover:from-amber-400 hover:to-rose-400 text-slate-950 transition cursor-pointer flex items-center gap-1 shadow">
                                <span>🏆</span> Bốc Thăm Tranh HCV & HCĐ
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <!-- Matches List -->
            @if ($matches->isEmpty())
                <div class="text-center py-12 bg-slate-900 border border-slate-800 rounded-xl text-slate-400">
                    <p class="text-base font-medium">
                        {{ $isSearching ? 'Không tìm thấy cặp đấu nào phù hợp với từ khóa.' : 'Chưa có cặp đấu nào trong vòng này.' }}
                    </p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($matches as $match)
                        <livewire:tournament.match-row :matchId="$match->id" :showContext="$isSearching" :key="'match-' . $match->id" />
                    @endforeach
                </div>
            @endif
        </div>
    @elseif ($activeTab === 'playoff')
        <!-- Playoff Tab View -->
        <livewire:tournament.playoff-board :tournamentId="$tournament->id" />
    @elseif ($activeTab === 'stage2')
        <!-- Stage 2 Board (7 rounds round-robin + combined standings) -->
        <livewire:tournament.stage2-board :tournamentId="$tournament->id" />
    @elseif ($activeTab === 'stepladder')
        <!-- Stepladder Finals Display -->
        <div class="space-y-6">
            @php
                $stepladderRounds = $tournament->stepladderRounds()->with('matches.games', 'matches.player1', 'matches.player2')->get();
                if ($stepladderRounds->isEmpty() && $tournament->format === 'stepladder_top5') {
                    $stepladderRounds = $tournament->rounds()->where('stage', 'knockout')->with('matches.games', 'matches.player1', 'matches.player2')->get();
                }
            @endphp
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6">
                <h2 class="text-xl font-black text-amber-400 flex items-center gap-2">
                    <span>👑</span> Vòng Chung Kết Stepladder Top 5
                </h2>
                <p class="text-xs text-slate-400 mt-1">Trận 1 (Hạng 2 vs 3), Trận 2 (Hạng 4 vs 5), Trận Tranh HCĐ và Trận Tranh HCV.</p>
            </div>
            @foreach ($stepladderRounds as $stRound)
                <div class="bg-slate-900/80 border border-slate-800 rounded-2xl p-6 space-y-4">
                    <span class="px-3 py-1 rounded-lg text-xs font-black uppercase tracking-wider bg-gradient-to-r from-amber-500 to-rose-500 text-slate-950 shadow">
                        {{ $stRound->stage_name }}
                    </span>
                    <div class="space-y-3">
                        @foreach ($stRound->matches as $match)
                            <livewire:tournament.match-row :matchId="$match->id" :key="'step-match-' . $match->id" />
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Standings Tab View -->
        <livewire:tournament.standings-board :tournamentId="$tournament->id" :selectedGroupId="$selectedGroupId" />
    @endif
</div>
