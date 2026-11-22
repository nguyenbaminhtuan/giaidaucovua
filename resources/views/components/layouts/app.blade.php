<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-900 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Hệ Thống Giải Đấu Cờ Vua' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-full flex flex-col font-sans bg-slate-950 text-slate-100 antialiased selection:bg-amber-500 selection:text-slate-950">
    <!-- Navbar -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="{{ route('home') }}" class="flex items-center gap-2.5 font-bold text-lg text-amber-400 hover:text-amber-300 transition">
                <span class="text-2xl">♞</span>
                <span>GiaiDauCo<span class="text-white">Vua</span></span>
            </a>

            <nav class="flex items-center gap-3">
                <a href="{{ route('tournament.index') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition">
                    📋 Danh Sách Giải
                </a>
                <a href="{{ route('tournament.create') }}" class="px-3.5 py-1.5 rounded-lg text-sm font-medium bg-amber-500 hover:bg-amber-400 text-slate-950 shadow-sm transition">
                    + Tạo giải đấu mới
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-6xl w-full mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800 py-6 text-center text-xs text-slate-500">
        Hệ Thống Quản Lý & Điều Hành Giải Đấu Cờ Vua • Laravel 11 + Livewire 3
    </footer>

    @livewireScripts
</body>
</html>
