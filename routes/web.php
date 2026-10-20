<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/transaksi', [TransactionController::class, 'index'])->name('transactions.index');
Route::post('/transaksi', [TransactionController::class, 'store'])->name('transactions.store');
Route::put('/transaksi/{transaction}', [TransactionController::class, 'update'])->name('transactions.update');
Route::delete('/transaksi/{transaction}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

Route::get('/laporan', [ReportController::class, 'index'])->name('reports.index');
Route::get('/laporan/ekspor', [ReportController::class, 'export'])->name('reports.export');

Route::get('/pengaturan', [SettingsController::class, 'index'])->name('settings.index');

Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

