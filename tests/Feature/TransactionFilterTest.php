<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionFilterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);
    }

    public function test_transaksi_dipaginasi_sebanyak_12_per_halaman(): void
    {
        Transaction::factory()->count(15)->create();

        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->total() === 15
                && $transactions->perPage() === 12
                && $transactions->count() === 12;
        });
    }

    public function test_halaman_kedua_menampilkan_sisa_transaksi(): void
    {
        Transaction::factory()->count(15)->create();

        $response = $this->get(route('transactions.index', ['page' => 2]));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->currentPage() === 2
                && $transactions->total() === 15
                && $transactions->count() === 3;
        });
    }

    public function test_pencarian_menyaring_transaksi(): void
    {
        Transaction::factory()->create(['title' => 'Belanja bulanan']);
        Transaction::factory()->create(['title' => 'Gaji bulanan']);

        $response = $this->get(route('transactions.index', ['search' => 'Gaji']));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->total() === 1
                && $transactions->first()->title === 'Gaji bulanan';
        });
    }

    public function test_filter_jenis_transaksi_berfungsi(): void
    {
        Transaction::factory()->count(2)->income()->create();
        Transaction::factory()->count(3)->expense()->create();

        $response = $this->get(route('transactions.index', ['type' => Transaction::TYPE_INCOME]));

        $response->assertOk();
        $response->assertViewHas('transactions', fn ($transactions) => $transactions->total() === 2);
    }

    public function test_filter_kategori_berfungsi(): void
    {
        Transaction::factory()->count(2)->create(['category' => 'Makanan']);
        Transaction::factory()->count(3)->create(['category' => 'Gaji']);

        $response = $this->get(route('transactions.index', ['category' => 'Makanan']));

        $response->assertOk();
        $response->assertViewHas('transactions', fn ($transactions) => $transactions->total() === 2);
    }

    public function test_filter_kombinasi_menerapkan_semua_kondisi(): void
    {
        Transaction::factory()->create([
            'title' => 'Gaji hari ini',
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
        ]);

        Transaction::factory()->create([
            'title' => 'Gaji bulan lalu',
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
        ]);

        Transaction::factory()->create([
            'title' => 'Makan hari ini',
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Makanan',
        ]);

        $response = $this->get(route('transactions.index', [
            'search' => 'Gaji',
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
        ]));

        $response->assertOk();
        $response->assertViewHas('transactions', function ($transactions): bool {
            return $transactions->total() === 2;
        });
    }

    public function test_total_tersaring_dihitung_benar(): void
    {
        Transaction::factory()->create([
            'type' => Transaction::TYPE_INCOME,
            'amount' => 1000000,
        ]);

        Transaction::factory()->create([
            'type' => Transaction::TYPE_EXPENSE,
            'amount' => 250000,
        ]);

        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewHas('filteredTotals', function (array $totals): bool {
            return $totals['income'] === 1000000.0
                && $totals['expense'] === 250000.0
                && $totals['balance'] === 750000.0
                && $totals['count'] === 2;
        });
    }

    public function test_total_kosong_menghasilkan_nol_bukan_null(): void
    {
        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewHas('filteredTotals', function (array $totals): bool {
            return $totals['income'] === 0.0
                && $totals['expense'] === 0.0
                && $totals['balance'] === 0.0
                && $totals['count'] === 0;
        });
    }

    public function test_filter_kosong_mengabaikan_nilai_tidak_valid(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->get(route('transactions.index', [
            'search' => '   ',
            'type' => 'tidak-valid',
            'category' => '',
        ]));

        $response->assertOk();
        $response->assertViewHas('transactions', fn ($transactions) => $transactions->total() === 3);
    }
}