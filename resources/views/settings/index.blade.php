@extends('layouts.app')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan')
@section('page_subtitle', 'Kelola kategori pemasukan dan pengeluaran')

@section('content')
    <div class="mb-5 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="neo-stat animate-rise" style="--accent: var(--color-electric);">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Total Transaksi</p>
                    <p class="mt-1.5 font-display text-2xl font-bold">{{ $transactionCount }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-electric text-white">
                    <i data-lucide="arrow-left-right" class="h-5 w-5"></i>
                </span>
            </div>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-emerald); animation-delay: 60ms;">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Kategori Pemasukan</p>
                    <p class="mt-1.5 font-display text-2xl font-bold">{{ $incomeCategories->count() }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-emerald text-ink">
                    <i data-lucide="trending-up" class="h-5 w-5"></i>
                </span>
            </div>
        </div>

        <div class="neo-stat animate-rise" style="--accent: var(--color-expense); animation-delay: 120ms;">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-[0.65rem] font-bold uppercase tracking-[0.12em] text-muted">Kategori Pengeluaran</p>
                    <p class="mt-1.5 font-display text-2xl font-bold">{{ $expenseCategories->count() }}</p>
                </div>
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border-2 border-black bg-expense text-white">
                    <i data-lucide="trending-down" class="h-5 w-5"></i>
                </span>
            </div>
        </div>
    </div>

    {{-- Tambah kategori --}}
    <div class="neo-card mb-5 animate-rise" style="animation-delay: 180ms;">
        <div class="border-b-[3px] border-black p-4 sm:p-5">
            <h2 class="font-display text-lg font-bold">Tambah Kategori</h2>
            <p class="text-xs text-muted">Kategori baru langsung tersedia di form transaksi.</p>
        </div>

        <form method="POST" action="{{ route('categories.store') }}" class="grid grid-cols-1 gap-3 p-4 sm:grid-cols-3 sm:p-5">
            @csrf

            <div>
                <label for="category-name" class="neo-label">Nama Kategori</label>
                <input type="text" id="category-name" name="name" value="{{ old('name') }}"
                       maxlength="100" required class="neo-input" placeholder="Contoh: Transportasi">
            </div>

            <div>
                <label for="category-type" class="neo-label">Jenis</label>
                <select id="category-type" name="type" required class="neo-input">
                    <option value="expense" {{ old('type', 'expense') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                    <option value="income" {{ old('type') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                </select>
            </div>

            <div class="flex items-end">
                <button type="submit" class="neo-btn neo-btn-primary neo-btn-sm w-full">
                    <i data-lucide="plus" class="h-4 w-4"></i>
                    Tambah Kategori
                </button>
            </div>
        </form>
    </div>

    {{-- Daftar kategori --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        @php
            $kelompok = [
                ['judul' => 'Kategori Pemasukan', 'ikon' => 'trending-up', 'kelas' => 'bg-emerald text-ink', 'data' => $incomeCategories],
                ['judul' => 'Kategori Pengeluaran', 'ikon' => 'trending-down', 'kelas' => 'bg-expense text-white', 'data' => $expenseCategories],
            ];
        @endphp

        @foreach ($kelompok as $kelompokKategori)
            <div class="neo-card animate-rise" style="animation-delay: 240ms;">
                <div class="flex items-center justify-between gap-2 border-b-[3px] border-black p-4 sm:p-5">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl border-2 border-black {{ $kelompokKategori['kelas'] }}">
                            <i data-lucide="{{ $kelompokKategori['ikon'] }}" class="h-4 w-4"></i>
                        </span>
                        <div>
                            <h2 class="font-display text-lg font-bold">{{ $kelompokKategori['judul'] }}</h2>
                            <p class="text-xs text-muted">{{ $kelompokKategori['data']->count() }} kategori</p>
                        </div>
                    </div>
                </div>

                @if ($kelompokKategori['data']->isEmpty())
                    <p class="p-8 text-center text-sm text-muted">Belum ada kategori. Tambahkan di atas.</p>
                @else
                    <ul class="divide-y-2 divide-slate-200">
                        @foreach ($kelompokKategori['data'] as $kategori)
                            @php
                                $dipakai = (int) ($categoryUsage[$kategori->name] ?? 0);
                            @endphp
                            <li class="flex items-center justify-between gap-3 p-4">
                                <div class="min-w-0">
                                    <p class="truncate font-semibold">{{ $kategori->name }}</p>
                                    <p class="text-xs text-muted">
                                        {{ $dipakai > 0 ? $dipakai.' transaksi' : 'Belum dipakai' }}
                                    </p>
                                </div>

                                <form method="POST" action="{{ route('categories.destroy', $kategori) }}" class="shrink-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            data-delete-category
                                            data-title="{{ $kategori->name }}"
                                            data-used="{{ $dipakai }}"
                                            class="neo-btn neo-btn-danger neo-btn-sm !px-2"
                                            title="Hapus kategori">
                                        <i data-lucide="trash-2" class="h-4 w-4"></i>
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endforeach
    </div>

    <div class="neo-alert neo-alert-error mt-5 animate-rise" style="animation-delay: 300ms;">
        <i data-lucide="info" class="h-5 w-5 shrink-0"></i>
        <span class="flex-1 text-sm">
            Menghapus kategori tidak menghapus transaksi yang sudah tercatat. Transaksi lama tetap memakai nama kategori tersebut.
        </span>
    </div>
@endsection
