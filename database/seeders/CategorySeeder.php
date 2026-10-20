<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * @var array<string, array<int, string>>
     */
    private const CATEGORIES = [
        'income' => [
            'Gaji',
            'Freelance',
            'Bisnis',
            'Hadiah',
            'Lainnya',
        ],
        'expense' => [
            'Makanan',
            'Transportasi',
            'Belanja',
            'Tagihan',
            'Pendidikan',
            'Hiburan',
            'Kesehatan',
            'Lainnya',
        ],
    ];

    public function run(): void
    {
        foreach (self::CATEGORIES as $type => $names) {
            foreach ($names as $name) {
                Category::query()->updateOrCreate(
                    ['name' => $name, 'type' => $type],
                    []
                );
            }
        }
    }
}
