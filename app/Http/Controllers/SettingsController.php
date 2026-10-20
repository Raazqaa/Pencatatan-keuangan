<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Transaction;

class SettingsController extends Controller
{
    public function index()
    {
        $incomeCategories = Category::query()
            ->where('type', Category::TYPE_INCOME)
            ->orderBy('name')
            ->get();

        $expenseCategories = Category::query()
            ->where('type', Category::TYPE_EXPENSE)
            ->orderBy('name')
            ->get();

        $categoryUsage = Transaction::query()
            ->selectRaw('category, COUNT(*) AS total')
            ->groupBy('category')
            ->pluck('total', 'category');

        return view('settings.index', [
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
            'categoryUsage' => $categoryUsage,
            'transactionCount' => Transaction::query()->count(),
        ]);
    }
}
