<?php

namespace Database\Seeders;

use App\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $rows = [
            // Pemasukan
            // ['title' => 'Gaji bulan ini', 'amount' => 8500000, 'type' => 'income', 'category' => 'Gaji', 'days' => 4, 'notes' => 'Gaji bulanan dari kantor'],
            // ['title' => 'Proyek website klien', 'amount' => 3500000, 'type' => 'income', 'category' => 'Freelance', 'days' => 2, 'notes' => 'Pembayaran tahap 2'],
            // ['title' => 'Bonus penjualan', 'amount' => 1200000, 'type' => 'income', 'category' => 'Bisnis', 'days' => 9, 'notes' => null],
            // ['title' => 'Hadiah ulang tahun', 'amount' => 500000, 'type' => 'income', 'category' => 'Hadiah', 'days' => 14, 'notes' => 'DariPDAT'],
            // ['title' => 'Jualan barang bekas', 'amount' => 275000, 'type' => 'income', 'category' => 'Lainnya', 'days' => 18, 'notes' => null],

            // // Pengeluaran
            // ['title' => 'Belanja bulanan Indomaret', 'amount' => 1250000, 'type' => 'expense', 'category' => 'Makanan', 'days' => 3, 'notes' => 'Kebutuhan rumah tangga'],
            // ['title' => 'Gojek ke kantor', 'amount' => 47000, 'type' => 'expense', 'category' => 'Transportasi', 'days' => 1, 'notes' => null],
            // ['title' => 'Beli sepatu lari', 'amount' => 899000, 'type' => 'expense', 'category' => 'Belanja', 'days' => 6, 'notes' => 'Nike Pegasus 40'],
            // ['title' => 'Tagihan listrik & wifi', 'amount' => 645000, 'type' => 'expense', 'category' => 'Tagihan', 'days' => 8, 'notes' => 'Listrik PLN + IndiHome'],
            // ['title' => 'Kursus online desain', 'amount' => 750000, 'type' => 'expense', 'category' => 'Pendidikan', 'days' => 11, 'notes' => null],
            // ['title' => 'Nonton bioskop', 'amount' => 120000, 'type' => 'expense', 'category' => 'Hiburan', 'days' => 5, 'notes' => 'Weekend'],
            // ['title' => 'Obat dan vitamin', 'amount' => 385000, 'type' => 'expense', 'category' => 'Kesehatan', 'days' => 12, 'notes' => 'Check-up rutin'],
            // ['title' => 'Kopi dan camilan', 'amount' => 68000, 'type' => 'expense', 'category' => 'Makanan', 'days' => 2, 'notes' => null],
            // ['title' => 'Parkir bulanan', 'amount' => 350000, 'type' => 'expense', 'category' => 'Transportasi', 'days' => 15, 'notes' => null],

            // // Riwayat bulan sebelumnya
            // ['title' => 'Gaji bulan lalu', 'amount' => 8500000, 'type' => 'income', 'category' => 'Gaji', 'days' => 34, 'notes' => null],
            // ['title' => 'Komisi penjualan', 'amount' => 2100000, 'type' => 'income', 'category' => 'Bisnis', 'days' => 30, 'notes' => null],
            // ['title' => 'Belanja bahan pokok', 'amount' => 980000, 'type' => 'expense', 'category' => 'Makanan', 'days' => 32, 'notes' => null],
            // ['title' => 'Bensin motor', 'amount' => 175000, 'type' => 'expense', 'category' => 'Transportasi', 'days' => 28, 'notes' => null],
            // ['title' => 'Pulsa internet', 'amount' => 150000, 'type' => 'expense', 'category' => 'Tagihan', 'days' => 26, 'notes' => null],
            // ['title' => 'Ganti sepatu kantor', 'amount' => 650000, 'type' => 'expense', 'category' => 'Belanja', 'days' => 22, 'notes' => null],

            // // Dua bulan sebelumnya
            // ['title' => 'Gaji dua bulan lalu', 'amount' => 8500000, 'type' => 'income', 'category' => 'Gaji', 'days' => 65, 'notes' => null],
            // ['title' => 'Proyek landed page', 'amount' => 2750000, 'type' => 'income', 'category' => 'Freelance', 'days' => 58, 'notes' => null],
            // ['title' => 'Tagihan listrik', 'amount' => 420000, 'type' => 'expense', 'category' => 'Tagihan', 'days' => 60, 'notes' => null],
            // ['title' => 'Makan malam keluarga', 'amount' => 375000, 'type' => 'expense', 'category' => 'Makanan', 'days' => 55, 'notes' => null],
            // ['title' => 'Langganan Aplikasi desain', 'amount' => 145000, 'type' => 'expense', 'category' => 'Pendidikan', 'days' => 50, 'notes' => null],
        ];

        foreach ($rows as $row) {
            Transaction::query()->create([
                'title' => $row['title'],
                'amount' => $row['amount'],
                'type' => $row['type'],
                'category' => $row['category'],
                'date' => $now->copy()->subDays($row['days'])->toDateString(),
                'notes' => $row['notes'],
            ]);
        }
    }
}
