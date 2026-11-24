<div class="space-y-6">
    <!-- Header & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-5">
        <div>
            <h1 class="text-2xl font-bold text-white flex items-center gap-2">
                <span>🏆</span> Danh Sách Giải Đấu Đã Tạo
            </h1>
            <p class="text-sm text-slate-400 mt-1">
                @if (session('is_admin'))
                    <span class="text-amber-400 font-semibold">👑 Chế độ Quản trị viên:</span> Bạn có toàn quyền tạo giải, sửa tên, ghi điểm và bốc thăm.
                @else
                    <span class="text-slate-300 font-semibold">👀 Chế độ Người xem:</span> Bạn có thể theo dõi lịch đấu, kết quả và bảng xếp hạng trực tiếp.
                @endif
            </p>
        </div>

        <div class="flex items-center gap-3">
            @if (session('is_admin'))
                <a href="{{ route('tournament.create') }}" class="px-4 py-2.5 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/10 flex items-center gap-1.5 transition">
                    <span>+</span> Tạo Giải Đấu Mới
                </a>
            @else
                <button type="button"
                        wire:click="$dispatch('open-admin-login', { redirectUrl: '{{ route('tournament.create') }}' })"
                        class="px-4 py-2.5 rounded-xl text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md shadow-amber-500/10 flex items-center gap-1.5 transition cursor-pointer"
                        title="Yêu cầu mã PIN 1210">
                    <span>+</span> Tạo Giải Đấu Mới
                </button>
            @endif
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

    <!-- Auth Notice if Redirected -->
    @if (session()->has('auth_required'))
        <div class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-sm font-medium flex items-center justify-between">
            <div class="flex items-center gap-2">
                <span>🔒</span>
                <span>{{ session('auth_required') }}</span>
            </div>
            <button type="button" wire:click="$dispatch('open-admin-login', { redirectUrl: '{{ route('tournament.create') }}' })" class="px-3 py-1 bg-amber-500 text-slate-950 font-bold text-xs rounded-lg hover:bg-amber-400 transition cursor-pointer">
                Nhập mã PIN ngay
            </button>
        </div>
    @endif

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
                @if (session('is_admin'))
                    <a href="{{ route('tournament.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-amber-500 hover:bg-amber-400 text-slate-950 transition">
                        + Tạo giải đấu ngay
                    </a>
                @else
                    <button type="button" wire:click="$dispatch('open-admin-login', { redirectUrl: '{{ route('tournament.create') }}' })" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm bg-amber-500 hover:bg-amber-400 text-slate-950 transition cursor-pointer">
                        + Tạo giải đấu ngay (Cần PIN)
                    </button>
                @endif
            </div>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($tournaments as $t)
                <div class="bg-slate-900 border border-slate-800 hover:border-slate-700 rounded-2xl p-5 flex flex-col justify-between transition shadow-sm hover:shadow-md">
                    <div class="space-y-3">
                        <!-- Format & Status Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider {{ $t->format === 'stepladder_top5' ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20' : ($t->format === 'round_robin_groups' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20') }}">
                                {{ $t->format === 'stepladder_top5' ? 'Chung Kết Top 5' : ($t->format === 'round_robin_groups' ? 'Vòng Tròn Chia Bảng' : 'Hệ Thụy Sĩ') }}
                            </span>

                            <span class="text-xs text-slate-500">
                                {{ $t->created_at->format('d/m/Y H:i') }}
                            </span>
                        </div>

                        <!-- Name & Edit -->
                        @if (session('is_admin') && $editingTournamentId === $t->id)
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
                                @if (session('is_admin'))
                                    <button type="button"
                                            wire:click="startEditing({{ $t->id }}, '{{ addslashes($t->name) }}')"
                                            class="text-slate-500 hover:text-amber-400 text-xs p-1 rounded transition"
                                            title="Chỉnh sửa tên giải">
                                        ✏️
                                    </button>
                                @endif
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
                        @if (session('is_admin'))
                            <button type="button"
                                    wire:click="confirmDelete({{ $t->id }}, '{{ addslashes($t->name) }}')"
                                    class="text-xs text-slate-500 hover:text-rose-400 transition cursor-pointer">
                                Xóa giải
                            </button>
                        @else
                            <span class="text-xs text-slate-500 italic">👀 Chế độ người xem</span>
                        @endif

                        <a href="{{ route('tournament.show', $t) }}" class="inline-flex items-center gap-1 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 transition">
                            <span>{{ session('is_admin') ? 'Vào quản lý & xem điểm' : 'Vào xem lịch & kết quả' }}</span>
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

    <!-- Delete Confirmation Modal with PIN -->
    @if ($deletingTournamentId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-rose-500/30 rounded-2xl p-6 max-w-md w-full shadow-2xl space-y-5">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center text-2xl flex-shrink-0">
                        ⚠️
                    </div>
                    <div class="space-y-1">
                        <h3 class="text-lg font-bold text-white">Xác nhận xóa giải đấu</h3>
                        <p class="text-xs text-slate-400">
                            Bạn đang yêu cầu xóa vĩnh viễn giải đấu:
                            <strong class="text-white block mt-1 font-semibold">"{{ $deletingTournamentName }}"</strong>
                        </p>
                    </div>
                </div>

                <div class="p-3 bg-rose-500/10 border border-rose-500/20 rounded-xl text-xs text-rose-300">
                    Cảnh báo: Toàn bộ bảng đấu, kỳ thủ, ván đấu và bảng xếp hạng của giải này sẽ bị xóa vĩnh viễn không thể khôi phục!
                </div>

                <form wire:submit="executeDelete" class="space-y-4">
                    <div class="space-y-2">
                        <label class="block text-xs font-semibold text-slate-300">
                            Nhập lại mã PIN quản trị để xác nhận xóa:
                        </label>
                        <input type="password"
                               maxlength="10"
                               wire:model="deletePinInput"
                               placeholder="Mã PIN..."
                               autofocus
                               class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-center text-white text-lg font-mono tracking-widest focus:outline-none focus:border-rose-500 transition">
                        @error('deletePinInput')
                            <span class="text-rose-400 text-xs block font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button"
                                wire:click="cancelDelete"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            Hủy bỏ
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-rose-600 hover:bg-rose-500 text-white shadow-lg shadow-rose-600/20 transition flex items-center gap-1.5 cursor-pointer">
                            <span>🗑️</span>
                            <span>Xác nhận xóa vĩnh viễn</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
