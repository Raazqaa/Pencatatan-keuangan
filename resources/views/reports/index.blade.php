@extends('layouts.app')

@section('title', 'Laporan')
@section('page_title', 'Laporan Keuangan')
@section('page_subtitle', 'Analisis pemasukan dan pengeluaran per periode')

@section('header_actions')
    <a href="{{ route('reports.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
       class="neo-btn neo-btn-success neo-btn-sm">
        <i data-lucide="download" class="h-4 w-4"></i>
        <span class="hidden sm:inline">Ekspor CSV</span>
    </a>
@endsection

@section('content')
    @php
        $bulan = [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    @endphp

    {{-- Filter periode --}}
    <form method="GET" action="{{ route('reports.index') }}" class="neo-card mb-5 flex flex-col gap-3 p-4 sm:flex-row sm:items-end">
        <div class="flex-1">
            <label for="month" class="neo-label">Bulan</label>
            <select id="month" name="month" class="neo-input" data-auto-submit>
                @foreach ($bulan as $number => $name)
                    <option value="{{ $number }}" {{ $number === $selectedMonth ? 'selected' : '' }}>{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1">
            <label for="year" class="neo-label">Tahun</label>
            <select id="year" name="year" class="neo-input" data-auto-submit>
                @foreach ($availableYears as $tahun)
                    <option value="{{ $tahun }}" {{ (int) $tahun === $selectedYear ? 'selected' : '' }}>{{ $tahun }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex gap-2">
            <button type="submit" class="neo-btn neo-btn-ghost neo-btn-sm flex-1 sm:flex-none">
                <i data-lucide="filter" class="h-4 w-4"></i>
                Terapkan
            </button>
            <a href="{{ route('reports.index') }}" class="neo-btn neo-btn-ghost neo-btn-sm flex-1 sm:flex-none">
                <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                Reset
            </a>
        </div>
    </form>

    {{-- Ringkasan --}}
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="neo-stat animate-rise" style="--accent: var(--color-emerald);">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Pemasukan</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold text-emerald-dark">
                        {{ \App\Support\Rupiah::format($totals['income']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-emerald text-ink">
                    <i data-lucide="arrow-down-to-line" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">Periode {{ $periodLabel }}</p>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-expense); animation-delay: 60ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Pengeluaran</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold text-expense-dark">
                        {{ \App\Support\Rupiah::format($totals['expense']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-expense text-white">
                    <i data-lucide="arrow-up-from-line" class="h-5 w-5"></i>
                </span>
            </div>
            <p class="mt-2 text-xs text-muted">{{ $totals['count'] }} transaksi tercatat</p>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-electric); animation-delay: 120ms;">
            <div class="flex items-start justify-between gap-2">
                <div class="min-w-0">
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Saldo / Selisih</p>
                    <p class="mt-1.5 truncate font-display text-2xl font-bold {{ $totals['balance'] >= 0 ? 'text-emerald-dark' : 'text-expense-dark' }}">
                        {{ \App\Support\Rupiah::format($totals['balance']) }}
                    </p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-electric text-white">
                    <i data-lucide="scale" class="h-5 w-5"></i>
                </span>
            </div>
            @php
                $rasio = $totals['income'] > 0 ? ($totals['expense'] / $totals['income']) * 100 : 0;
            @endphp
            <p class="mt-2 text-xs text-muted">Pengeluaran {{ number_format($rasio, 1) }}% dari pemasukan</p>
        </div>
    </div>

    {{-- Grafik --}}
    <div class="mb-5 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <div class="neo-card animate-rise p-4 sm:p-5 xl:col-span-2" style="animation-delay: 180ms;">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold">Tren 6 Bulan Terakhir</h2>
                    <p class="text-xs text-muted">Pemasukan dan pengeluaran per bulan</p>
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

        <div class="neo-card animate-rise p-4 sm:p-5" style="animation-delay: 240ms;">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-display text-lg font-bold">Pengeluaran per Kategori</h2>
                    <p class="text-xs text-muted">Komposisi {{ $periodLabel }}</p>
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

    {{-- Detail kategori --}}
    <div class="neo-card animate-rise mb-5" style="animation-delay: 300ms;">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b-[3px] border-black p-4 sm:p-5">
            <div>
                <h2 class="font-display text-lg font-bold">Rincian Kategori Pengeluaran</h2>
                <p class="text-xs text-muted">{{ $periodLabel }}</p>
            </div>
        </div>

        @if ($categoryBreakdown->isEmpty())
            <p class="p-8 text-center text-sm text-muted">Tidak ada pengeluaran pada periode ini.</p>
        @else
            @php
                $totalKategori = (float) $categoryBreakdown->sum('total');
            @endphp
            <div class="neo-scroll overflow-x-auto">
                <table class="neo-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Persentase</th>
                            <th class="w-1/3">Proporsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categoryBreakdown as $row)
                            @php
                                $persentase = $totalKategori > 0 ? ((float) $row->total / $totalKategori) * 100 : 0;
                            @endphp
                            <tr>
                                <td>
                                    <span class="neo-badge neo-badge-expense">{{ $row->category }}</span>
                                </td>
                                <td class="whitespace-nowrap text-right font-display font-bold">
                                    {{ \App\Support\Rupiah::format($row->total) }}
                                </td>
                                <td class="text-right font-semibold">{{ number_format($persentase, 1) }}%</td>
                                <td>
                                    <div class="h-3 w-full overflow-hidden rounded-full border-2 border-black bg-white">
                                        <div class="h-full bg-expense" style="width: {{ max($persentase, 2) }}%"></div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-ink text-white">
                            <td class="px-4 py-3 font-display font-bold">Total</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right font-display font-bold">
                                {{ \App\Support\Rupiah::format($totalKategori) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold">100%</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    {{-- Tabel transaksi periode --}}
    <div class="neo-card animate-rise" style="animation-delay: 360ms;">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b-[3px] border-black p-4 sm:p-5">
            <div>
                <h2 class="font-display text-lg font-bold">Detail Transaksi</h2>
                <p class="text-xs text-muted">{{ $transactions->count() }} transaksi pada {{ $periodLabel }}</p>
            </div>
            <a href="{{ route('reports.export', ['month' => $selectedMonth, 'year' => $selectedYear]) }}"
               class="neo-btn neo-btn-success neo-btn-sm">
                <i data-lucide="file-spreadsheet" class="h-4 w-4"></i>
                Ekspor CSV
            </a>
        </div>

        @if ($transactions->isEmpty())
            <p class="p-10 text-center text-sm text-muted">Tidak ada transaksi pada {{ $periodLabel }}.</p>
        @else
            <div class="neo-scroll overflow-x-auto">
                <table class="neo-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Transaksi</th>
                            <th>Jenis</th>
                            <th>Kategori</th>
                            <th class="text-right">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaksi)
                            <tr>
                                <td class="whitespace-nowrap text-muted">{{ $transaksi->date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="font-semibold">{{ $transaksi->title }}</span>
                                    @if ($transaksi->notes)
                                        <span class="mt-0.5 block max-w-sm truncate text-xs text-muted">{{ $transaksi->notes }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="neo-badge {{ $transaksi->isIncome() ? 'neo-badge-income' : 'neo-badge-expense' }}">
                                        {{ $transaksi->isIncome() ? 'Pemasukan' : 'Pengeluaran' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap">{{ $transaksi->category }}</td>
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
@endsection
