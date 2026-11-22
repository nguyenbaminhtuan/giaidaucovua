<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span>🏆</span> Danh Sách Giải Đấu Đã Tạo
            </h1>
            <p class="text-sm text-slate-400 mt-1">Quản lý các giải cờ vua đang diễn ra, cập nhật kết quả và bảng xếp hạng.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('tournament.create') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/10 flex items-center gap-1.5 transition">
                <span>+</span> Tạo Giải Đấu Mới
            </a>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="flex items-center gap-3 bg-slate-900 border border-slate-800 rounded-xl px-4 py-2.5">
        <span class="text-slate-500">🔍</span>
        <input type="text"
               wire:model.live.debounce.300ms="search"
               placeholder="Tìm kiếm giải đấu theo tên..."
               class="bg-transparent border-none text-white text-sm w-full focus:outline-none placeholder:text-slate-500">
    </div>

    <!-- Flash Message -->
    @if (session()->has('message'))
        <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm font-medium flex items-center justify-between">
            <span>✓ {{ session('message') }}</span>
        </div>
    @endif

    <!-- Tournaments Grid -->
    @if ($tournaments->isEmpty())
        <div class="text-center py-16 bg-slate-900/50 border border-slate-800 rounded-2xl p-8 space-y-4">
            <div class="text-4xl">♟️</div>
            <h2 class="text-lg font-bold text-white">Chưa có giải đấu nào được tạo</h2>
            <p class="text-sm text-slate-400 max-w-md mx-auto">
                Bắt đầu bằng việc tạo giải đấu mới với thể thức Đấu vòng tròn chia bảng hoặc Hệ Thụy Sĩ.
            </p>
            <div>
                <a href="{{ route('tournament.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-amber-500 hover:bg-amber-400 text-slate-950 transition">
                    + Tạo giải đấu ngay
                </a>
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($tournaments as $t)
                <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 flex flex-col justify-between transition shadow-sm hover:shadow-md">
                    <div class="space-y-3">
                        <!-- Format & Status Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider {{ $t->format === 'round_robin_groups' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' }}">
                                {{ $t->format === 'round_robin_groups' ? 'Vòng Tròn Chia Bảng' : 'Hệ Thụy Sĩ' }}
                            </span>

                            <span class="text-xs text-slate-500">
                                {{ $t->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Name & Edit -->
                        @if ($editingTournamentId === $t->id)
                            <form wire:submit="saveName" class="flex items-center gap-2">
                                <input type="text"
                                       wire:model="editingName"
                                       class="bg-slate-950 border border-amber-500 rounded px-2.5 py-1 text-sm font-bold text-white w-full focus:outline-none">
                                <button type="submit" class="px-2.5 py-1 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold rounded">
                                    Lưu
                                </button>
                                <button type="button" wire:click="cancelEditing" class="px-2 py-1 bg-slate-800 text-slate-300 text-xs rounded">
                                    Hủy
                                </button>
                            </form>
                            @error('editingName') <span class="text-rose-400 text-xs">{{ $message }}</span> @enderror
                        @else
                            <div class="flex items-center justify-between gap-2">
                                <h2 class="text-lg font-bold text-white leading-snug hover:text-amber-400 transition truncate">
                                    <a href="{{ route('tournament.show', $t) }}">
                                        {{ $t->name }}
                                    </a>
                                </h2>
                                <button type="button"
                                        wire:click="startEditing({{ $t->id }}, '{{ addslashes($t->name) }}')"
                                        class="text-slate-500 hover:text-amber-400 text-xs p-1 rounded transition"
                                        title="Chỉnh sửa tên giải">
                                    ✏️
                                </button>
                            </div>
                        @endif

                        <!-- Stats Pills -->
                        <div class="flex flex-wrap items-center gap-2 text-xs text-slate-300">
                            <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 flex items-center gap-1.5 font-medium">
                                <span>👥</span> {{ $t->participants_count }} Kỳ thủ
                            </span>

                            @if ($t->format === 'round_robin_groups')
                                <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 flex items-center gap-1.5 font-medium">
                                    <span>🗂</span> {{ $t->groups_count }} Bảng đấu
                                </span>
                            @endif

                            <span class="px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 flex items-center gap-1.5 font-medium">
                                <span>🔄</span> {{ $t->rounds_count }} Vòng đấu
                            </span>
                        </div>
                    </div>

                    <!-- Footer Actions -->
                    <div class="border-t border-slate-800/80 pt-4 mt-4 flex items-center justify-between">
                        <button type="button"
                                wire:click="deleteTournament({{ $t->id }})"
                                wire:confirm="Bạn có chắc chắn muốn xóa giải '{{ $t->name }}' không? Toàn bộ dữ liệu cặp đấu và điểm số sẽ bị xóa vĩnh viễn."
                                class="text-xs text-slate-500 hover:text-rose-400 transition">
                            Xóa giải
                        </button>

                        <a href="{{ route('tournament.show', $t) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                            <span>Vào quản lý & xem điểm</span>
                            <span>➔</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="pt-2">
            {{ $tournaments->links() }}
        </div>
    @endif
</div>
