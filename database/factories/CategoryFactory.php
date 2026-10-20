<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => ucfirst($this->faker->unique()->word()),
            'type' => Category::TYPE_EXPENSE,
        ];
    }

    public function income(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_INCOME,
        ]);
    }

    public function expense(): static
    {
        return $this->state(fn () => [
            'type' => Category::TYPE_EXPENSE,
        ]);
    }
}