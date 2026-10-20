<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public const TYPE_INCOME = 'income';

    public const TYPE_EXPENSE = 'expense';

    protected $fillable = [
        'name',
        'type',
    ];

    /**
     * @return array<int, string>
     */
    public static function optionsFor(string $type): array
    {
        return static::query()
            ->where('type', $type)
            ->orderBy('name')
            ->pluck('name')
            ->all();
    }
}
