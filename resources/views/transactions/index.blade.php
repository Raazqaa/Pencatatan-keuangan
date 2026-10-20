@extends('layouts.app')

@section('title', 'Transaksi')
@section('page_title', 'Transaksi')
@section('page_subtitle', 'Kelola seluruh catatan pemasukan dan pengeluaran')

@section('header_actions')
    <button type="button"
            data-modal-open
            data-action="{{ route('transactions.store') }}"
            data-heading="Tambah Transaksi"
            data-submit-label="Simpan Transaksi"
            class="neo-btn neo-btn-primary neo-btn-sm">
        <i data-lucide="plus" class="h-4 w-4"></i>
        <span class="hidden sm:inline">Tambah</span>
    </button>
@endsection

@section('content')
    {{-- Filter & pencarian --}}
    <form method="GET" action="{{ route('transactions.index') }}" class="neo-card mb-5 p-4">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <label for="search" class="neo-label">Cari Transaksi</label>
                <div class="relative">
                    <i data-lucide="search" class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted"></i>
                    <input type="search" id="search" name="search" value="{{ $filters['search'] ?? '' }}"
                           class="neo-input pl-9" placeholder="Cari nama transaksi...">
                </div>
            </div>

            <div>
                <label for="type" class="neo-label">Jenis</label>
                <select id="type" name="type" class="neo-input" data-auto-submit>
                    <option value="">Semua jenis</option>
                    <option value="income" {{ ($filters['type'] ?? '') === 'income' ? 'selected' : '' }}>Pemasukan</option>
                    <option value="expense" {{ ($filters['type'] ?? '') === 'expense' ? 'selected' : '' }}>Pengeluaran</option>
                </select>
            </div>

            <div>
                <label for="category" class="neo-label">Kategori</label>
                <select id="category" name="category" class="neo-input" data-auto-submit>
                    <option value="">Semua kategori</option>
                    @foreach (array_merge($incomeCategories, $expenseCategories) as $kategori)
                        <option value="{{ $kategori }}" {{ ($filters['category'] ?? '') === $kategori ? 'selected' : '' }}>
                            {{ $kategori }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="month" class="neo-label">Bulan</label>
                <input type="month" id="month" name="month" value="{{ $filters['month'] ?? '' }}" class="neo-input" data-auto-submit>
            </div>
        </div>

        <div class="mt-3 flex flex-wrap gap-2">
            <button type="submit" class="neo-btn neo-btn-primary neo-btn-sm">
                <i data-lucide="filter" class="h-4 w-4"></i>
                Terapkan Filter
            </button>
            <a href="{{ route('transactions.index') }}" class="neo-btn neo-btn-ghost neo-btn-sm">
                <i data-lucide="rotate-ccw" class="h-4 w-4"></i>
                Reset
            </a>
        </div>
    </form>

    {{-- Ringkasan hasil filter --}}
    <div class="mb-4 grid grid-cols-2 gap-3 lg:grid-cols-4">
        <div class="neo-card-sm px-3 py-2.5">
            <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Jumlah Transaksi</p>
            <p class="mt-0.5 font-display text-lg font-bold">{{ $filteredTotals['count'] }}</p>
        </div>
        <div class="neo-card-sm px-3 py-2.5">
            <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Pemasukan</p>
            <p class="mt-0.5 truncate font-display text-lg font-bold text-emerald-dark">{{ \App\Support\Rupiah::compact($filteredTotals['income']) }}</p>
        </div>
        <div class="neo-card-sm px-3 py-2.5">
            <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Pengeluaran</p>
            <p class="mt-0.5 truncate font-display text-lg font-bold text-expense-dark">{{ \App\Support\Rupiah::compact($filteredTotals['expense']) }}</p>
        </div>
        <div class="neo-card-sm px-3 py-2.5">
            <p class="text-[0.6rem] font-bold uppercase tracking-[0.12em] text-muted">Selisih</p>
            <p class="mt-0.5 truncate font-display text-lg font-bold {{ $filteredTotals['balance'] >= 0 ? 'text-emerald-dark' : 'text-expense-dark' }}">
                {{ \App\Support\Rupiah::compact($filteredTotals['balance']) }}
            </p>
        </div>
    </div>

    {{-- Tabel transaksi --}}
    <div class="neo-card animate-rise">
        @if ($transactions->isEmpty())
            <div class="p-10 text-center">
                <span class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl border-2 border-black bg-white shadow-[4px_4px_0_0_#000]">
                    <i data-lucide="inbox" class="h-7 w-7 text-muted"></i>
                </span>
                <p class="font-display text-lg font-bold">Tidak ada transaksi</p>
                <p class="mx-auto mt-1 max-w-sm text-sm text-muted">
                    @if (array_filter($filters))
                        Tidak ada transaksi yang cocok dengan filter. Coba ubah kata kunci atau atur ulang filter.
                    @else
                        Belum ada transaksi tercatat. Klik tombol "Tambah" untuk mencatat transaksi pertama.
                    @endif
                </p>
            </div>
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
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($transactions as $transaksi)
                            <tr>
                                <td class="whitespace-nowrap text-muted">{{ $transaksi->date->format('d/m/Y') }}</td>
                                <td>
                                    <span class="font-semibold">{{ $transaksi->title }}</span>
                                    @if ($transaksi->notes)
                                        <span class="mt-0.5 block max-w-xs truncate text-xs text-muted">{{ $transaksi->notes }}</span>
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
                                <td>
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button type="button"
                                                data-edit-transaction
                                                data-action="{{ route('transactions.update', $transaksi) }}"
                                                data-title="{{ $transaksi->title }}"
                                                data-amount="{{ (float) $transaksi->amount }}"
                                                data-type="{{ $transaksi->type }}"
                                                data-category="{{ $transaksi->category }}"
                                                data-date="{{ $transaksi->date->format('Y-m-d') }}"
                                                data-notes="{{ $transaksi->notes }}"
                                                class="neo-btn neo-btn-ghost neo-btn-sm !px-2"
                                                title="Edit transaksi">
                                            <i data-lucide="pencil" class="h-4 w-4"></i>
                                        </button>

                                        <form method="POST"
                                              action="{{ route('transactions.destroy', $transaksi) }}"
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    data-delete-transaction
                                                    data-title="{{ $transaksi->title }}"
                                                    class="neo-btn neo-btn-danger neo-btn-sm !px-2"
                                                    title="Hapus transaksi">
                                                <i data-lucide="trash-2" class="h-4 w-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="border-t-[3px] border-black p-4">
                    <div class="neo-pagination">
                        @if ($transactions->onFirstPage())
                            <span aria-disabled="true" class="opacity-60">
                                <i data-lucide="chevron-left" class="h-4 w-4"></i>
                            </span>
                        @else
                            <a href="{{ $transactions->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                                <i data-lucide="chevron-left" class="h-4 w-4"></i>
                            </a>
                        @endif

                        <span aria-current="page">{{ $transactions->currentPage() }}</span>

                        @if ($transactions->hasMorePages())
                            <a href="{{ $transactions->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                                <i data-lucide="chevron-right" class="h-4 w-4"></i>
                            </a>
                        @else
                            <span aria-disabled="true" class="opacity-60">
                                <i data-lucide="chevron-right" class="h-4 w-4"></i>
                            </span>
                        @endif
                    </div>

                    <p class="mt-3 text-center text-xs text-muted">
                        Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
                        dari {{ $transactions->total() }} transaksi
                    </p>
                </div>
            @endif
        @endif
    </div>

    {{-- Modal tambah / edit transaksi --}}
    <div data-modal="transaction" class="neo-modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="transaction-modal-title">
        <div class="neo-modal-panel">
            <div class="flex items-center justify-between gap-3 border-b-[3px] border-black bg-ink px-4 py-3">
                <h2 data-modal-title id="transaction-modal-title" class="font-display text-lg font-bold text-white">
                    Tambah Transaksi
                </h2>
                <button type="button" data-modal-close class="neo-btn neo-btn-ghost neo-btn-sm !px-2" aria-label="Tutup modal">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>

            <form data-modal-form method="POST" action="{{ route('transactions.store') }}" class="space-y-4 p-4 sm:p-5">
                @csrf
                <input type="hidden" name="_method" data-modal-method value="POST">

                <div>
                    <label for="modal-title-field" class="neo-label">Nama Transaksi</label>
                    <input type="text" id="modal-title-field" name="title" maxlength="255" required
                           class="neo-input" placeholder="Contoh: Gaji bulanan">
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="modal-type" class="neo-label">Jenis</label>
                        <select id="modal-type" name="type" class="neo-input" required>
                            <option value="income">Pemasukan</option>
                            <option value="expense">Pengeluaran</option>
                        </select>
                    </div>

                    <div>
                        <label for="modal-category" class="neo-label">Kategori</label>
                        <select id="modal-category" name="category" class="neo-input" required
                                data-categories-income="{{ json_encode($incomeCategories) }}"
                                data-categories-expense="{{ json_encode($expenseCategories) }}">
                            <option value="">Pilih kategori</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="modal-amount" class="neo-label">Nominal (Rp)</label>
                        <input type="text" id="modal-amount" name="amount" required inputmode="numeric"
                               data-amount-input class="neo-input" placeholder="0">
                    </div>

                    <div>
                        <label for="modal-date" class="neo-label">Tanggal</label>
                        <input type="date" id="modal-date" name="date" required
                               value="{{ now()->format('Y-m-d') }}" class="neo-input">
                    </div>
                </div>

                <div>
                    <label for="modal-notes" class="neo-label">Catatan (opsional)</label>
                    <textarea id="modal-notes" name="notes" rows="3" maxlength="1000"
                              class="neo-input" placeholder="Tambahkan catatan singkat..."></textarea>
                </div>

                <div class="flex flex-col-reverse gap-2 pt-1 sm:flex-row sm:justify-end">
                    <button type="button" data-modal-close class="neo-btn neo-btn-ghost neo-btn-sm">
                        Batal
                    </button>
                    <button type="submit" data-modal-submit class="neo-btn neo-btn-primary neo-btn-sm">
                        Simpan Transaksi
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
