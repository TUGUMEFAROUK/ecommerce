<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $change = fake()->numberBetween(1, 20) * (fake()->boolean() ? 1 : -1);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'change' => $change,
            'stock_after' => max(0, fake()->numberBetween(0, 200)),
            'reason' => fake()->optional()->sentence(4),
        ];
    }
}
