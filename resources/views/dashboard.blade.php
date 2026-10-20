@extends('layouts.app')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan keuangan dan aktivitas terbaru')

@section('header_actions')
    <a href="{{ route('transactions.index') }}" class="neo-btn neo-btn-primary neo-btn-sm">
        <i data-lucide="plus" class="h-4 w-4"></i>
        <span class="hidden sm:inline">Transaksi Baru</span>
    </a>
@endsection

@section('content')
    @php
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $tahunTersedia = collect($availableMonths)->pluck('value')
            ->map(fn ($v) => (int) substr($v, 0, 4))
            ->unique()->sortDesc()->values();
        if (! in_array($selectedYear, $tahunTersedia->all(), true)) {
            $tahunTersedia->push($selectedYear);
            $tahunTersedia = $tahunTersedia->sortDesc()->values();
        }
    @endphp

    {{-- Filter periode --}}
    <form method="GET" action="{{ route('dashboard') }}"
          class="neo-card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="month" class="neo-label">Bulan</label>
            <select id="month" name="month" class="neo-input" data-auto-submit>
                @foreach ($bulan as $number => $name)
                    <option value="{{ $number }}" {{ $number === $selectedMonth ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex-1">
            <label for="year" class="neo-label">Tahun</label>
            <select id="year" name="year" class="neo-input" data-auto-submit>
                @foreach ($tahunTersedia as $tahun)
                    <option value="{{ $tahun }}" {{ (int) $tahun === $selectedYear ? 'selected' : '' }}>
                        {{ $tahun }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="neo-btn neo-btn-ghost neo-btn-sm flex-1 sm:flex-none">
                <i data-lucide="filter" class="h-4 w-4"></i>
                Terapkan
            </button>
            <a href="{{ route('dashboard') }}" class="neo-btn neo-btn-ghost neo-btn-sm flex-1 sm:flex-none">
                <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                Reset
            </a>
        </div>
    </form>

    {{-- Empat statistik utama --}}
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="neo-stat animate-rise" style="--accent: var(--color-electric); animation-delay: 0ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Total Saldo</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold {{ $overall['balance'] >= 0 ? 'text-emerald-dark' : 'text-expense-dark' }}">
                        {{ \App\Support\Rupiah::format($overall['balance']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-electric text-white">
                    <i data-lucide="wallet" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">Akumulasi {{ $overall['count'] }} transaksi</p>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-emerald); animation-delay: 60ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Pemasukan</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold text-emerald-dark">
                        {{ \App\Support\Rupiah::format($monthlyTotals['income']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-emerald text-ink">
                    <i data-lucide="arrow-down-to-line" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">Periode {{ $currentMonthLabel }}</p>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-expense); animation-delay: 120ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Pengeluaran</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold text-expense-dark">
                        {{ \App\Support\Rupiah::format($monthlyTotals['expense']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-expense text-white">
                    <i data-lucide="arrow-up-from-line" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">Periode {{ $currentMonthLabel }}</p>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-amber); animation-delay: 180ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Tingkat Tabungan</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold {{ $savingRate >= 0 ? 'text-electric' : 'text-expense-dark' }}">
                        {{ number_format($savingRate, 1) }}%
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-amber text-ink">
                    <i data-lucide="piggy-bank" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">Sisa {{ \App\Support\Rupiah::format($monthlyTotals['balance']) }} dari pemasukan</p>
        </div>
    </div>

    {{-- Perbandingan dengan bulan sebelumnya --}}
    @php
        $selisihPemasukan = $monthlyTotals['income'] - $previousTotals['income'];
        $selisihPengeluaran = $monthlyTotals['expense'] - $previousTotals['expense'];
    @endphp
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="neo-card-sm flex items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-emerald text-ink">
                    <i data-lucide="arrow-down-to-line" class="h-4 w-4"></i>
                </span>
                <div>
                    <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Pemasukan vs Bulan Lalu</p>
                    <p class="font-display text-sm font-bold {{ $selisihPemasukan >= 0 ? 'text-emerald-dark' : 'text-expense-dark' }}">
                        {{ $selisihPemasukan >= 0 ? '+' : '-' }}{{ \App\Support\Rupiah::compact(abs($selisihPemasukan)) }}
                    </p>
                </div>
            </div>
            <span class="text-xs text-muted">Bulan lalu: {{ \App\Support\Rupiah::compact($previousTotals['income']) }}</span>
        </div>

        <div class="neo-card-sm flex items-center justify-between gap-3 px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border-2 border-black bg-expense text-white">
                    <i data-lucide="arrow-up-from-line" class="h-4 w-4"></i>
                </span>
                <div>
                    <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Pengeluaran vs Bulan Lalu</p>
                    <p class="font-display text-sm font-bold {{ $selisihPengeluaran <= 0 ? 'text-emerald-dark' : 'text-expense-dark' }}">
                        {{ $selisihPengeluaran >= 0 ? '+' : '-' }}{{ \App\Support\Rupiah::compact(abs($selisihPengeluaran)) }}
                    </p>
                </div>
            </div>
            <span class="text-xs text-muted">Bulan lalu: {{ \App\Support\Rupiah::compact($previousTotals['expense']) }}</span>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="neo-card animate-rise p-4 sm:p-5 xl:col-span-2" style="animation-delay: 240ms;">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold">Arus Kas 6 Bulan Terakhir</h2>
                    <p class="text-xs text-muted">Perbandingan pemasukan dan pengeluaran bulanan</p>
                </div>
                <span class="neo-badge neo-badge-neutral">
                    <i data-lucide="bar-chart-3" class="h-3 w-3"></i>
                    Bulanan
                </span>
            </div>

            <div class="chart-box h-72 sm:h-80">
                <canvas data-chart="bar"
                        data-labels="{{ json_encode(array_column($monthlyChart, 'label')) }}"
                        data-income="{{ json_encode(array_map('floatval', array_column($monthlyChart, 'income'))) }}"
                        data-expense="{{ json_encode(array_map('floatval', array_column($monthlyChart, 'expense'))) }}"></canvas>
            </div>
        </div>

        <div class="neo-card animate-rise p-4 sm:p-5" style="animation-delay: 300ms;">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold">Komposisi Pengeluaran</h2>
                    <p class="text-xs text-muted">Per kategori, {{ $currentMonthLabel }}</p>
                </div>
                <span class="neo-badge neo-badge-neutral">
                    <i data-lucide="pie-chart" class="h-3 w-3"></i>
                    Kategori
                </span>
            </div>

            <div class="chart-box h-72 sm:h-80">
                <canvas data-chart="doughnut"
                        data-labels="{{ json_encode($categoryBreakdown->pluck('category')->values()) }}"
                        data-values="{{ json_encode($categoryBreakdown->pluck('total')->map(fn ($v) => (float) $v)->values()) }}"></canvas>
            </div>
        </div>
    </div>

    {{-- Ringkasan kategori & transaksi terbaru --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        <div class="neo-card animate-rise p-4 sm:p-5" style="animation-delay: 360ms;">
            <h2 class="mb-4 font-display text-lg font-bold">Ringkasan Kategori</h2>

            @if ($categoryBreakdown->isEmpty())
                <p class="rounded-xl border-2 border-dashed border-slate-300 p-6 text-center text-sm text-muted">
                    Belum ada pengeluaran pada {{ $currentMonthLabel }}.
                </p>
            @else
                @php
                    $totalKategori = (float) $categoryBreakdown->sum('total');
                @endphp
                <ul class="space-y-2.5">
                    @foreach ($categoryBreakdown->take(6) as $row)
                        @php
                            $persentase = $totalKategori > 0 ? ((float) $row->total / $totalKategori) * 100 : 0;
                        @endphp
                        <li>
                            <div class="mb-1 flex items-center justify-between gap-2 text-sm">
                                <span class="truncate font-semibold">{{ $row->category }}</span>
                                <span class="shrink-0 font-display text-xs font-bold">
                                    {{ \App\Support\Rupiah::compact($row->total) }}
                                    <span class="text-muted">({{ number_format($persentase, 0) }}%)</span>
                                </span>
                            </div>
                            <div class="h-2.5 w-full overflow-hidden rounded-full border-2 border-black bg-white">
                                <div class="h-full bg-expense" style="width: {{ max($persentase, 1.5) }}%"></div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="neo-card animate-rise lg:col-span-2" style="animation-delay: 420ms;">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b-[3px] border-black p-4 sm:p-5">
                <div>
                    <h2 class="font-display text-lg font-bold">Transaksi Terbaru</h2>
                    <p class="text-xs text-muted">5 transaksi terakhir yang dicatat</p>
                </div>
                <a href="{{ route('transactions.index') }}" class="neo-btn neo-btn-ghost neo-btn-sm">
                    Lihat semua
                    <i data-lucide="arrow-right" class="h-4 w-4"></i>
                </a>
            </div>

            @if ($recent->isEmpty())
                <p class="p-8 text-center text-sm text-muted">Belum ada transaksi. Mulai catat keuanganmu.</p>
            @else
                <div class="neo-scroll overflow-x-auto">
                    <table class="neo-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Transaksi</th>
                                <th>Kategori</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($recent as $transaksi)
                                <tr>
                                    <td class="whitespace-nowrap text-muted">{{ $transaksi->date->format('d/m/Y') }}</td>
                                    <td><span class="font-semibold">{{ $transaksi->title }}</span></td>
                                    <td>
                                        <span class="neo-badge {{ $transaksi->isIncome() ? 'neo-badge-income' : 'neo-badge-expense' }}">
                                            {{ $transaksi->category }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap text-right font-display font-bold {{ $transaksi->isIncome() ? 'text-emerald-dark' : 'text-expense-dark' }}">
                                        {{ $transaksi->isIncome() ? '+' : '-' }}{{ \App\Support\Rupiah::format($transaksi->amount) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection

