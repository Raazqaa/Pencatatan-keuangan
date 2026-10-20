<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FinanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
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

        $totals = $this->finance->totalsBetween($start, $end);
        $categoryBreakdown = $this->finance->expenseByCategory($start, $end);

        $transactions = Transaction::query()
            ->betweenPeriod($start, $end)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $availableYears = Transaction::distinctYears();

        if (! in_array($year, $availableYears, true)) {
            $availableYears[] = $year;
            rsort($availableYears);
        }

        return view('reports.index', [
            'totals' => $totals,
            'categoryBreakdown' => $categoryBreakdown,
            'transactions' => $transactions,
            'monthlyChart' => $this->finance->monthlyChart(6, $end),
            'selectedMonth' => $month,
            'selectedYear' => $year,
            'periodLabel' => $start->translatedFormat('F Y'),
            'availableYears' => $availableYears,
        ]);
    }

    public function export(Request $request): StreamedResponse
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

        $transactions = Transaction::query()
            ->betweenPeriod($start, $end)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $filename = 'fintrack-transaksi-'.$year.'-'.str_pad((string) $month, 2, '0', STR_PAD_LEFT).'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ];

        return response()->streamDownload(function () use ($transactions) {
            $handle = fopen('php://output', 'wb');

            // BOM agar Excel membaca UTF-8 dengan benar.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Tanggal', 'Nama Transaksi', 'Jenis', 'Kategori', 'Nominal', 'Catatan']);

            foreach ($transactions as $transaction) {
                fputcsv($handle, [
                    $transaction->date->format('Y-m-d'),
                    $transaction->title,
                    $transaction->isIncome() ? 'Pemasukan' : 'Pengeluaran',
                    $transaction->category,
                    number_format((float) $transaction->amount, 0, ',', '.'),
                    (string) $transaction->notes,
                ]);
            }

            fclose($handle);
        }, $filename, $headers);
    }
}
