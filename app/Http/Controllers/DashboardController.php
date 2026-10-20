<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function __construct(private readonly FinanceService $finance)
    {
    }

    public function index(Request $request)
    {
        $now = Carbon::now();

        $month = (int) $request->integer('month', $now->month);
        $year = (int) $request->integer('year', $now->year);

        if ($month < 1 || $month > 12) {
            $month = $now->month;
        }

        if ($year < 2000 || $year > $now->year + 1) {
            $year = $now->year;
        }

        $start = Carbon::create($year, $month, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        $monthlyTotals = $this->finance->totalsBetween($start, $end);
        $overall = $this->finance->overallTotals();

        $recent = Transaction::query()
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $categoryBreakdown = $this->finance->expenseByCategory($start, $end);

        $previousStart = $start->copy()->subMonth()->startOfMonth();
        $previousTotals = $this->finance->totalsBetween($previousStart, $previousStart->copy()->endOfMonth());

        $availableMonths = Transaction::distinctMonths()
            ->map(fn (array $row) => [
                'value' => $row['y'].'-'.str_pad((string) $row['m'], 2, '0', STR_PAD_LEFT),
                'label' => Carbon::create((int) $row['y'], (int) $row['m'], 1)->translatedFormat('F Y'),
            ]);

        $savingRate = $monthlyTotals['income'] > 0
            ? ($monthlyTotals['balance'] / $monthlyTotals['income']) * 100
            : 0.0;

        return view('dashboard', [
            'overall' => $overall,
            'monthlyTotals' => $monthlyTotals,
            'previousTotals' => $previousTotals,
            'monthlyChart' => $this->finance->monthlyChart(6, $end),
            'categoryBreakdown' => $categoryBreakdown,
            'recent' => $recent,
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'currentMonthLabel' => $start->translatedFormat('F Y'),
            'availableMonths' => $availableMonths,
            'savingRate' => $savingRate,
        ]);
    }
}
