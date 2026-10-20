<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory;

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'title',
        'amount',
        'type',
        'category',
        'date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    /**
     * Batasi hasil query ke dalam rentang tanggal (inklusif di kedua ujung).
     *
     * Kolom `date` disimpan sebagai teks datetime (`Y-m-d H:i:s`) pada SQLite
     * dan sebagai kolom DATE pada MySQL. Karena itu perbandingan memakai
     * batas eksklusif di sisi atas agar konsisten di kedua database:
     * `date >= 2025-01-01` dan `date < 2025-02-01`.
     *
     * Batas bawah memakai format tanggal tanpa jam sehingga baris yang
     * tersimpan sebagai `2025-01-01 00:00:00` maupun `2025-01-01` sama-sama
     * terbawa, sedangkan batas atas memakai hari berikutnya agar transaksi
     * pada hari terakhir periode tidak terlewat.
     */
    public function scopeBetweenPeriod(Builder $query, Carbon $start, Carbon $end): Builder
    {
        return $query
            ->where('date', '>=', $start->copy()->startOfDay()->toDateString())
            ->where('date', '<', $end->copy()->startOfDay()->addDay()->toDateString());
    }

    /**
     * Scope transaksi pada satu bulan kalender tertentu.
     */
    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        $start = Carbon::create($year, $month, 1)->startOfMonth();

        return $query->betweenPeriod($start, $start->copy()->endOfMonth());
    }

    /**
     * Daftar kombinasi tahun/bulan yang memiliki transaksi.
     *
     * Sengaja tidak memakai fungsi `YEAR()`/`MONTH()` karena hanya tersedia
     * di MySQL. Perhitungan dilakukan di PHP agar hasilnya identik pada
     * SQLite maupun MySQL.
     *
     * @return \Illuminate\Support\Collection<int, array{y: int, m: int}>
     */
    public static function distinctMonths(): \Illuminate\Support\Collection
    {
        return static::query()
            ->orderBy('date')
            ->distinct()
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date))
            ->map(fn (Carbon $date) => ['y' => $date->year, 'm' => $date->month])
            ->unique(fn (array $row) => $row['y'].'-'.$row['m'])
            ->sortByDesc(fn (array $row) => $row['y'].'-'.str_pad((string) $row['m'], 2, '0', STR_PAD_LEFT))
            ->values();
    }

    /**
     * Daftar tahun yang memiliki transaksi (urutan menurun).
     *
     * @return array<int, int>
     */
    public static function distinctYears(): array
    {
        return static::distinctMonths()
            ->pluck('y')
            ->unique()
            ->values()
            ->all();
    }

    public function isIncome(): bool
    {
        return $this->type === self::TYPE_INCOME;
    }

    public function isExpense(): bool
    {
        return $this->type === self::TYPE_EXPENSE;
    }

    /**
     * Nominal positif untuk pemasukan, negatif untuk pengeluaran.
     */
    public function getSignedAmountAttribute(): float
    {
        return $this->isIncome()
            ? (float) $this->amount
            : -1 * (float) $this->amount;
    }
}
