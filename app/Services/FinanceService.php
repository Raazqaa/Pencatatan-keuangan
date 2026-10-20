<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class FinanceService
{
    /**
     * Total keseluruhan seluruh transaksi.
     *
     * @return array{income: float, expense: float, balance: float, count: int}
     */
    public function overallTotals(): array
    {
        $row = Transaction::query()
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense")
            ->selectRaw('COUNT(*) AS total')
            ->first();

        $income = (float) ($row->income ?? 0);
        $expense = (float) ($row->expense ?? 0);

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => (int) ($row->total ?? 0),
        ];
    }

    /**
     * Total untuk rentang tanggal tertentu.
     *
     * @return array{income: float, expense: float, balance: float, count: int}
     */
    public function totalsBetween(Carbon $start, Carbon $end): array
    {
        $row = Transaction::query()
            ->betweenPeriod($start, $end)
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) AS income")
            ->selectRaw("COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) AS expense")
            ->selectRaw('COUNT(*) AS total')
            ->first();

        $income = (float) ($row->income ?? 0);
        $expense = (float) ($row->expense ?? 0);

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense,
            'count' => (int) ($row->total ?? 0),
        ];
    }

    /**
     * Data bar chart pemasukan & pengeluaran per bulan.
     *
     * @return array<int, array{label: string, income: float, expense: float}>
     */
    public function monthlyChart(int $months = 6, ?Carbon $until = null): array
    {
        $until = $until ? $until->copy() : Carbon::now();
        $start = $until->copy()->startOfMonth()->subMonths($months - 1);

        $rows = Transaction::query()
            ->betweenPeriod($start, $until->copy()->endOfMonth())
            ->get(['date', 'type', 'amount']);

        $grouped = $rows->groupBy(fn (Transaction $t) => $t->date->format('Y-m'));

        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $cursor = $start->copy()->addMonths($i);
            $key = $cursor->format('Y-m');
            $bucket = $grouped->get($key, collect());

            $data[] = [
                'label' => $this->monthLabel($cursor),
                'income' => (float) $bucket->where('type', Transaction::TYPE_INCOME)->sum('amount'),
                'expense' => (float) $bucket->where('type', Transaction::TYPE_EXPENSE)->sum('amount'),
            ];
        }

        return $data;
    }

    /**
     * Total pengeluaran per kategori.
     *
     * @return Collection<int, object>
     */
    public function expenseByCategory(?Carbon $start = null, ?Carbon $end = null): Collection
    {
        $query = Transaction::query()
            ->where('type', Transaction::TYPE_EXPENSE)
            ->selectRaw('category, SUM(amount) AS total')
            ->groupBy('category');

        if ($start && $end) {
            $query->betweenPeriod($start, $end);
        }

        return $query->orderByDesc('total')->get();
    }

    /**
     * Top kategori pengeluaran.
     *
     * @return object|null
     */
    public function topExpenseCategory(?Carbon $start = null, ?Carbon $end = null): ?object
    {
        return $this->expenseByCategory($start, $end)->first();
    }

    private function monthLabel(Carbon $date): string
    {
        $months = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun',
            7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        return $months[$date->month].' '.$date->year;
    }
}
