<?php

namespace Database\Factories;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    protected $model = Transaction::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $isIncome = $this->faker->boolean(40);

        return [
            'title' => Str::ucfirst($this->faker->words(3, true)),
            'amount' => $this->faker->randomFloat(2, 10, 5_000_000),
            'type' => $isIncome ? Transaction::TYPE_INCOME : Transaction::TYPE_EXPENSE,
            'category' => $isIncome ? 'Gaji' : 'Makanan',
            'date' => Carbon::now()->subDays($this->faker->numberBetween(0, 60))->toDateString(),
            'notes' => $this->faker->boolean(60) ? $this->faker->sentence() : null,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_INCOME,
            'category' => 'Gaji',
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => Transaction::TYPE_EXPENSE,
            'category' => 'Makanan',
        ]);
    }

    public function onDate(Carbon|string $date): static
    {
        return $this->state(fn () => [
            'date' => $date instanceof Carbon ? $date->toDateString() : $date,
        ]);
    }
}