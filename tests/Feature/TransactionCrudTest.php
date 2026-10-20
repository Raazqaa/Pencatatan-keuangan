<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);
    }

    public function test_halaman_daftar_transaksi_dapat_diakses(): void
    {
        Transaction::factory()->count(3)->create();

        $response = $this->get(route('transactions.index'));

        $response->assertOk();
        $response->assertViewHas('transactions');
    }

    public function test_transaksi_baru_dapat_disimpan(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => 'Gaji bulan November',
            'amount' => 8500000,
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
            'date' => '2025-11-05',
            'notes' => 'Gaji bulanan',
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'title' => 'Gaji bulan November',
            'amount' => 8500000,
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
        ]);

        // Tanggal disimpan sebagai kolom `date`; bandingkan lewat cast model
        // agar assertion tetap valid untuk SQLite maupun MySQL.
        $stored = Transaction::query()->where('title', 'Gaji bulan November')->sole();

        $this->assertSame('2025-11-05', $stored->date->toDateString());
        $this->assertSame('Gaji bulanan', $stored->notes);
    }

    public function test_transaksi_dapat_diperbarui(): void
    {
        $transaction = Transaction::factory()->create([
            'title' => 'Makan siang',
            'amount' => 25000,
        ]);

        $response = $this->put(route('transactions.update', $transaction), [
            'title' => 'Makan siang dirichlet',
            'amount' => 30000,
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Makanan',
            'date' => '2025-11-06',
            'notes' => null,
        ]);

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'title' => 'Makan siang dirichlet',
            'amount' => 30000,
        ]);
    }

    public function test_transaksi_dapat_dihapus(): void
    {
        $transaction = Transaction::factory()->create();

        $response = $this->delete(route('transactions.destroy', $transaction));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    public function test_validasi_menolak_transaksi_tanpa_wajib_diisi(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => '',
            'amount' => '',
            'type' => '',
            'category' => '',
            'date' => '',
        ]);

        $response->assertSessionHasErrors(['title', 'amount', 'type', 'category', 'date']);
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_nominal_nol_ditolak(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => 'Transaksi nol',
            'amount' => 0,
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Makanan',
            'date' => '2025-11-05',
        ]);

        $response->assertSessionHasErrors('amount');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_kategori_tidak_tersedia_untuk_jenis_transaksi_ditolak(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => 'Transaksi kategori salah',
            'amount' => 1000,
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Gaji',
            'date' => '2025-11-05',
        ]);

        $response->assertSessionHasErrors('category');
        $this->assertDatabaseCount('transactions', 0);
    }

    public function test_format_tanggal_tidak_valid_ditolak(): void
    {
        $response = $this->post(route('transactions.store'), [
            'title' => 'Transaksi tanggal salah',
            'amount' => 1000,
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Makanan',
            'date' => '05-11-2025',
        ]);

        $response->assertSessionHasErrors('date');
        $this->assertDatabaseCount('transactions', 0);
    }
}