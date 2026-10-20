<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_pengaturan_menampilkan_kategori_pemasukan_dan_pengeluaran(): void
    {
        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);

        $response = $this->get(route('settings.index'));

        $response->assertOk();
        $response->assertViewHas('incomeCategories', function ($categories): bool {
            return $categories->count() === 1 && $categories->first()->name === 'Gaji';
        });
        $response->assertViewHas('expenseCategories', function ($categories): bool {
            return $categories->count() === 1 && $categories->first()->name === 'Makanan';
        });
    }

    public function test_kategori_baru_dapat_disimpan(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => 'Investasi',
            'type' => Category::TYPE_INCOME,
        ]);

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Investasi',
            'type' => Category::TYPE_INCOME,
        ]);
    }

    public function test_kategori_dapat_dihapus(): void
    {
        $category = Category::factory()->create(['name' => 'Transportasi']);

        $response = $this->delete(route('categories.destroy', $category));

        $response->assertRedirect(route('settings.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_kategori_dengan_nama_sama_dan_jenis_sama_ditolak(): void
    {
        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);

        $response = $this->post(route('categories.store'), [
            'name' => 'Gaji',
            'type' => Category::TYPE_INCOME,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 1);
    }

    public function test_nama_sama_dengan_jenis_berbeda_boleh_disimpan(): void
    {
        Category::factory()->create(['name' => 'Lain', 'type' => Category::TYPE_INCOME]);

        $response = $this->post(route('categories.store'), [
            'name' => 'Lain',
            'type' => Category::TYPE_EXPENSE,
        ]);

        $response->assertRedirect(route('settings.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Lain',
            'type' => Category::TYPE_EXPENSE,
        ]);
    }

    public function test_kategori_tanpa_nama_ditolak(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => '',
            'type' => Category::TYPE_INCOME,
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_jenis_kategori_tidak_valid_ditolak(): void
    {
        $response = $this->post(route('categories.store'), [
            'name' => 'Ngawur',
            'type' => 'transfer',
        ]);

        $response->assertSessionHasErrors('type');
        $this->assertDatabaseCount('categories', 0);
    }

    public function test_pengaturan_menampilkan_jumlah_penggunaan_kategori(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);

        Transaction::factory()->count(3)->create(['category' => 'Makanan']);

        $response = $this->get(route('settings.index'));

        $response->assertOk();
        $response->assertViewHas('categoryUsage', function ($usage) use ($category): bool {
            return (int) $usage['Makanan'] === 3;
        });
        $response->assertViewHas('transactionCount', 3);
    }

    public function test_kategori_terpakai_tetap_menyimpan_nama_pada_transaksi_lama(): void
    {
        $category = Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);

        $transaction = Transaction::factory()->create(['category' => 'Makanan']);

        $this->delete(route('categories.destroy', $category));

        // Transaksi lama tetap memakai nama kategori meski kategorinya dihapus.
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'category' => 'Makanan',
        ]);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_halaman_pengaturan_menandai_kategori_yang_sudah_dipakai(): void
    {
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);
        Transaction::factory()->count(2)->create(['category' => 'Makanan']);

        $response = $this->get(route('settings.index'));

        $response->assertOk();
        $response->assertSee('2 transaksi');
        $response->assertSee('data-used="2"', false);
    }
}