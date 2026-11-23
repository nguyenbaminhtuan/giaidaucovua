<div class="flex items-center gap-3">
    @if ($isAdmin)
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40">
                <span>👑</span>
                <span>Quản Trị Viên</span>
            </span>

            <button type="button" wire:click="logout" class="text-xs text-slate-400 hover:text-rose-400 transition ml-1" title="Thoát chế độ quản trị">
                [Thoát]
            </button>
        </div>

        <a href="{{ route('tournament.create') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-sm transition">
            + Tạo giải đấu mới
        </a>
    @else
        <button type="button"
                wire:click="openLogin"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition cursor-pointer">
            <span>🔑</span>
            <span>Quản trị</span>
        </button>

        <button type="button"
                wire:click="clickCreateTournament"
                class="px-3.5 py-1.5 rounded-lg text-sm font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-sm transition cursor-pointer">
            + Tạo giải đấu mới
        </button>
    @endif

    <!-- Admin PIN Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
            <div class="bg-slate-900 border border-amber-500/30 rounded-2xl p-6 max-w-sm w-full shadow-2xl space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-800 pb-3">
                    <span class="w-10 h-10 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-xl">
                        🔑
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-white">Xác Thực Quản Trị Viên</h3>
                        <p class="text-xs text-slate-400">Nhập mã PIN để thao tác tạo giải và chỉnh sửa.</p>
                    </div>
                </div>

                <form wire:submit="verifyPin" class="space-y-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-300 text-center">
                            Nhập mã PIN 4 số:
                        </label>
                        <input type="password"
                               maxlength="10"
                               wire:model="adminPin"
                               placeholder="••••"
                               autofocus
                               class="w-full bg-slate-950 border border-slate-700 rounded-lg px-4 py-2.5 text-center text-white text-xl font-mono tracking-widest focus:outline-none focus:border-amber-500 transition">
                        @error('adminPin')
                            <span class="text-rose-400 text-xs block text-center font-medium">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-1">
                        <button type="button"
                                wire:click="closeLogin"
                                class="px-4 py-2 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
                            Hủy
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-xs font-bold bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-md transition cursor-pointer">
                            Xác nhận
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
