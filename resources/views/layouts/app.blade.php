<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="FinTrack — aplikasi pencatatan keuangan pribadi dengan dashboard, laporan, dan grafik interaktif.">

    <title>@yield('title', 'Dashboard') — FinTrack</title>

    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect width='100' height='100' rx='18' fill='%23121820'/><path d='M28 66V34h10l12 18 12-18h10v32H62V50L50 68 38 50v16z' fill='%2310B981'/></svg>">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-ink text-ink antialiased">
    <div class="flex min-h-screen">
        {{-- Sidebar --}}
        <aside
            data-sidebar
            class="neo-sidebar neo-scroll fixed inset-y-0 left-0 z-70 flex w-68 flex-col overflow-y-auto border-r-[3px] border-black bg-ink px-4 py-5 lg:static lg:translate-x-0"
        >
            <a href="{{ route('dashboard') }}" class="mb-6 flex items-center gap-3 px-2">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl border-[3px] border-black bg-emerald shadow-[4px_4px_0_0_#000]">
                    <i data-lucide="wallet" class="h-6 w-6 text-ink"></i>
                </span>
                <span>
                    <span class="block font-display text-xl font-bold tracking-tight text-white">FinTrack</span>
                    <span class="block text-[0.65rem] font-semibold uppercase tracking-[0.18em] text-slate-400">Personal Finance</span>
                </span>
            </a>

            <nav class="flex flex-1 flex-col gap-1.5">
                <p class="px-3 pb-1 text-[0.65rem] font-bold uppercase tracking-[0.18em] text-slate-500">Menu</p>

                <a href="{{ route('dashboard') }}"
                   class="neo-sidebar-link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                    <i data-lucide="layout-dashboard" class="h-5 w-5 shrink-0"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('transactions.index') }}"
                   class="neo-sidebar-link {{ request()->routeIs('transactions.*') ? 'is-active' : '' }}">
                    <i data-lucide="arrow-left-right" class="h-5 w-5 shrink-0"></i>
                    <span>Transaksi</span>
                </a>

                <a href="{{ route('reports.index') }}"
                   class="neo-sidebar-link {{ request()->routeIs('reports.*') ? 'is-active' : '' }}">
                    <i data-lucide="file-bar-chart" class="h-5 w-5 shrink-0"></i>
                    <span>Laporan</span>
                </a>

                <a href="{{ route('settings.index') }}"
                   class="neo-sidebar-link {{ request()->routeIs('settings.*') || request()->routeIs('categories.*') ? 'is-active' : '' }}">
                    <i data-lucide="settings" class="h-5 w-5 shrink-0"></i>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <div class="mt-6 rounded-xl border-2 border-ink-line bg-ink-soft p-4">
                <div class="flex items-center gap-2 text-emerald">
                    <i data-lucide="trending-up" class="h-4 w-4"></i>
                    <span class="text-[0.65rem] font-bold uppercase tracking-[0.15em]">T Dollars</span>
                </div>
                <p class="mt-2 text-xs leading-relaxed text-slate-400">
                    Catat pemasukan dan pengeluaran harianmu, lalu pantau pertumbuhan keuangan lewat dashboard interaktif.
                </p>
            </div>
        </aside>

        {{-- Overlay mobile --}}
        <div data-sidebar-overlay class="fixed inset-0 z-60 hidden bg-ink/70 backdrop-blur-sm lg:hidden"></div>


        {{-- Konten --}}
        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-50 flex items-center gap-3 border-b-[3px] border-black bg-card px-4 py-3 sm:px-6">
                <button type="button"
                        data-sidebar-toggle
                        class="neo-btn neo-btn-ghost neo-btn-sm lg:hidden"
                        aria-label="Buka menu navigasi">
                    <i data-lucide="menu" class="h-5 w-5"></i>
                </button>

                <div class="min-w-0 flex-1">
                    <h1 class="truncate font-display text-lg font-bold sm:text-xl">@yield('page_title', 'Dashboard')</h1>
                    @hasSection('page_subtitle')
                        <p class="truncate text-xs text-muted sm:text-sm">@yield('page_subtitle')</p>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    @yield('header_actions')
                    <span class="hidden items-center gap-2 rounded-xl border-2 border-black bg-white px-3 py-1.5 sm:flex">
                        <i data-lucide="calendar-days" class="h-4 w-4 text-electric"></i>
                        <span class="font-display text-xs font-bold">{{ now()->translatedFormat('d M Y') }}</span>
                    </span>
                </div>
            </header>

            <main class="flex-1 px-4 py-5 sm:px-6 sm:py-6">
                {{-- Flash message --}}
                @if (session('success'))
                    <div data-flash class="neo-alert neo-alert-success mb-5 animate-rise" role="status">
                        <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0"></i>
                        <span class="flex-1">{{ session('success') }}</span>
                        <button type="button" data-flash-close class="shrink-0 opacity-70 transition hover:opacity-100" aria-label="Tutup">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div data-flash class="neo-alert neo-alert-error mb-5 animate-rise" role="alert">
                        <i data-lucide="alert-octagon" class="h-5 w-5 shrink-0"></i>
                        <span class="flex-1">{{ session('error') }}</span>
                        <button type="button" data-flash-close class="shrink-0 opacity-70 transition hover:opacity-100" aria-label="Tutup">
                            <i data-lucide="x" class="h-4 w-4"></i>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="neo-alert neo-alert-error mb-5 animate-rise" role="alert">
                        <i data-lucide="alert-octagon" class="h-5 w-5 shrink-0"></i>
                        <div class="flex-1">
                            <p class="font-display font-bold">Terjadi kesalahan</p>
                            <ul class="mt-1 list-inside list-disc text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="border-t-[3px] border-black bg-ink px-4 py-4 sm:px-6">
                <p class="text-center text-xs text-slate-400">
                    FinTrack — pencatatan keuangan pribadi sederhana. Dibangun dengan Laravel 12, Tailwind CSS v4 &amp; Chart.js.
                </p>
            </footer>
        </div>
    </div>
</body>
</html>
