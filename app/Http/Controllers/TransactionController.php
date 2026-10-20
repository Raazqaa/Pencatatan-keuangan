<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query();

        // Pencarian berdasarkan nama transaksi.
        if ($search = trim((string) $request->query('search', ''))) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        // Filter jenis transaksi.
        if ($type = $request->query('type')) {
            if (in_array($type, [Transaction::TYPE_INCOME, Transaction::TYPE_EXPENSE], true)) {
                $query->where('type', $type);
            }
        }

        // Filter kategori.
        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        // Filter bulan (opsional, format YYYY-MM).
        if ($month = $request->query('month')) {
            try {
                $parsed = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
                $query->betweenPeriod($parsed, $parsed->copy()->endOfMonth());
            } catch (\Throwable) {
                // Abaikan filter bulan yang tidak valid.
            }
        }

        $transactions = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        $filteredTotals = (clone $query)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense")
            ->selectRaw('COUNT(*) AS total')
            ->first();

        return view('transactions.index', [
            'transactions' => $transactions,
            'filteredTotals' => [
                'income' => (float) ($filteredTotals->income ?? 0),
                'expense' => (float) ($filteredTotals->expense ?? 0),
                'balance' => (float) ($filteredTotals->income ?? 0) - (float) ($filteredTotals->expense ?? 0),
                'count' => (int) ($filteredTotals->total ?? 0),
            ],
            'incomeCategories' => Category::optionsFor(Category::TYPE_INCOME),
            'expenseCategories' => Category::optionsFor(Category::TYPE_EXPENSE),
            'filters' => $request->only(['search', 'type', 'category', 'month']),
        ]);
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['amount'] = (float) $data['amount'];
        $data['notes'] = $data['notes'] ?? null;

        Transaction::query()->create($data);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi "'.$data['title'].'" berhasil ditambahkan.');
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $data = $request->validated();
        $data['amount'] = (float) $data['amount'];
        $data['notes'] = $data['notes'] ?? null;

        $transaction->update($data);

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi "'.$data['title'].'" berhasil diperbarui.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $title = $transaction->title;
        $transaction->delete();

        return redirect()
            ->route('transactions.index')
            ->with('success', 'Transaksi "'.$title.'" berhasil dihapus.');
    }
}
