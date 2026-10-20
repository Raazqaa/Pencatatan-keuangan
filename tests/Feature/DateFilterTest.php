<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DateFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);
    }

    public function test_scope_antara_periode_mencakup_kedua_ujung(): void
    {
        Transaction::factory()->onDate('2025-11-01')->create(['title' => 'Awal']);
        Transaction::factory()->onDate('2025-11-15')->create(['title' => 'Tengah']);
        Transaction::factory()->onDate('2025-11-30')->create(['title' => 'Akhir']);
        Transaction::factory()->onDate('2025-12-01')->create(['title' => 'Luaran']);

        $results = Transaction::query()
            ->betweenPeriod(Carbon::parse('2025-11-01'), Carbon::parse('2025-11-30'))
            ->pluck('title')
            ->all();

        $this->assertCount(3, $results);
        $this->assertContains('Awal', $results);
        $this->assertContains('Akhir', $results);
        $this->assertNotContains('Luaran', $results);
    }

    public function test_scope_antara_periode_mencakup_transaksi_pada_hari_terakhir(): void
    {
        // Transaksi terakhir bulan November harus ikut terhitung.
        Transaction::factory()->onDate('2025-11-30')->create(['title' => 'Akhir November']);

        $count = Transaction::query()
            ->betweenPeriod(Carbon::parse('2025-11-01'), Carbon::parse('2025-11-30'))
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_scope_antara_periode_tidak_menggabungkan_tahun_berbeda(): void
    {
        Transaction::factory()->onDate('2025-12-31')->create(['title' => 'Akhir Tahun']);
        Transaction::factory()->onDate('2026-01-01')->create(['title' => 'Awal Tahun']);

        $count = Transaction::query()
            ->betweenPeriod(Carbon::parse('2025-12-01'), Carbon::parse('2025-12-31'))
            ->count();

        $this->assertSame(1, $count);
    }

    public function test_scope_bulan_menyaring_transaksi_satu_bulan(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create(['title' => 'November']);
        Transaction::factory()->onDate('2025-10-31')->create(['title' => 'Oktober']);
        Transaction::factory()->onDate('2025-12-01')->create(['title' => 'Desember']);

        $count = Transaction::query()->inMonth(2025, 11)->count();

        $this->assertSame(1, $count);
    }

    public function test_daftar_transaksi_memfilter_berdasarkan_bulan(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create(['title' => 'November']);
        Transaction::factory()->onDate('2025-10-20')->create(['title' => 'Oktober']);

        $response = $this->get(route('transactions.index', ['month' => '2025-11']));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->total() === 1
                && $transactions->first()->title === 'November';
        });
    }

    public function test_daftar_transaksi_mengabaikan_filter_bulan_tidak_valid(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create();
        Transaction::factory()->onDate('2025-10-20')->create();

        $response = $this->get(route('transactions.index', ['month' => 'bukan-bulan']));

        $response->assertOk();
        $response->assertViewHas('transactions', fn ($transactions) => $transactions->total() === 2);
    }

    public function test_dashboard_menampilkan_total_bulan_yang_dipilih(): void
    {
        Transaction::factory()->onDate('2025-11-10')->expense()->create([
            'title' => 'Pengeluaran November',
            'amount' => 500000,
        ]);

        Transaction::factory()->onDate('2025-10-10')->expense()->create([
            'title' => 'Pengeluaran Oktober',
            'amount' => 900000,
        ]);

        $response = $this->get(route('dashboard', ['month' => 11, 'year' => 2025]));

        $response->assertOk();
        $response->assertViewHas('monthlyTotals', function (array $totals): bool {
            return $totals['expense'] === 500000.0
                && $totals['income'] === 0.0
                && $totals['count'] === 1;
        });
    }

    public function test_laporan_menampilkan_transaksi_bulan_yang_dipilih(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create(['title' => 'November']);
        Transaction::factory()->onDate('2025-10-10')->create(['title' => 'Oktober']);

        $response = $this->get(route('reports.index', ['month' => 11, 'year' => 2025]));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->count() === 1
                && $transactions->first()->title === 'November';
        });
    }

    public function test_daftar_bulan_tersedia_dihitung_di_php_hingga_portabel_untuk_sqlite(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create();
        Transaction::factory()->onDate('2025-10-10')->create();
        Transaction::factory()->onDate('2024-11-10')->create();

        $months = Transaction::distinctMonths()->all();

        $this->assertSame(
            [['y' => 2025, 'm' => 11], ['y' => 2025, 'm' => 10], ['y' => 2024, 'm' => 11]],
            $months
        );

        $this->assertSame([2025, 2024], Transaction::distinctYears());
    }

    public function test_dashboard_menampilkan_bulan_tersedia_tanpa_fungsi_mysql(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create();
        Transaction::factory()->onDate('2025-09-10')->create();

        $response = $this->get(route('dashboard'));

        $response->assertOk();
        $response->assertViewHas('availableMonths', function ($months): bool {
            $values = $months->pluck('value')->all();

            return in_array('2025-11', $values, true)
                && in_array('2025-09', $values, true);
        });
    }
}
