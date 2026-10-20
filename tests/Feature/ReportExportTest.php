<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportExportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Category::factory()->create(['name' => 'Gaji', 'type' => Category::TYPE_INCOME]);
        Category::factory()->create(['name' => 'Makanan', 'type' => Category::TYPE_EXPENSE]);
    }

    public function test_ekspor_csv_menghasilkan_berkas_dengan_nama_yang_benar(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create(['title' => 'Gaji November']);

        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 2025]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString(
            'attachment',
            (string) $response->headers->get('content-disposition')
        );
        $this->assertStringContainsString(
            'fintrack-transaksi-2025-11.csv',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_isi_csv_memuat_header_dan_data_transaksi(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create([
            'title' => 'Gaji November',
            'amount' => 8500000,
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
            'notes' => 'Gaji bulanan',
        ]);

        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 2025]));

        $csv = $response->streamedContent();

        // BOM UTF-8 ditulis di awal berkas agar Excel membaca karakter dengan benar.
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Tanggal', $csv);
        $this->assertStringContainsString('Nama Transaksi', $csv);
        $this->assertStringContainsString('2025-11-10', $csv);
        $this->assertStringContainsString('Gaji November', $csv);
        $this->assertStringContainsString('Pemasukan', $csv);
        $this->assertStringContainsString('8.500.000', $csv);
        $this->assertStringContainsString('Gaji bulanan', $csv);
    }

    public function test_ekspor_hanya_memuat_transaksi_bulan_yang_dipilih(): void
    {
        Transaction::factory()->onDate('2025-11-10')->create(['title' => 'November']);
        Transaction::factory()->onDate('2025-10-10')->create(['title' => 'Oktober']);

        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 2025]));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('November', $csv);
        $this->assertStringNotContainsString('Oktober', $csv);
    }

    public function test_ekspor_menyertakan_transaksi_pada_hari_terakhir_bulan(): void
    {
        Transaction::factory()->onDate('2025-11-30')->create(['title' => 'Akhir November']);

        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 2025]));

        $this->assertStringContainsString('Akhir November', $response->streamedContent());
    }

    public function test_ekspor_dengan_bulan_tidak_valid_memakai_bulan_saat_ini(): void
    {
        $now = now();

        $response = $this->get(route('reports.export', ['month' => 99, 'year' => 2025]));

        // Bulan tidak valid diganti bulan saat ini, tahun yang valid tetap dipakai.
        $expected = 'fintrack-transaksi-2025-'.str_pad((string) $now->month, 2, '0', STR_PAD_LEFT).'.csv';

        $response->assertOk();
        $this->assertStringContainsString(
            $expected,
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_ekspor_dengan_tahun_tidak_valid_memakai_tahun_saat_ini(): void
    {
        $now = now();

        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 1990]));

        // Tahun tidak valid diganti tahun saat ini, bulan yang valid tetap dipakai.
        $expected = 'fintrack-transaksi-'.$now->year.'-11.csv';

        $response->assertOk();
        $this->assertStringContainsString(
            $expected,
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_ekspor_tanpa_data_tetap_menghasilkan_header(): void
    {
        $response = $this->get(route('reports.export', ['month' => 11, 'year' => 2025]));

        $csv = $response->streamedContent();

        $this->assertStringContainsString('Tanggal', $csv);
        $this->assertStringContainsString('Nama Transaksi', $csv);
        $this->assertStringContainsString('Catatan', $csv);
    }
}