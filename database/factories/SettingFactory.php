<?php

namespace Database\Factories;

use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * Tests override the key and value attributes to target any specific
     * setting, e.g. `Setting::factory()->create(['key' => 'low_stock_threshold', 'value' => '2'])`.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => 'store_name',
            'value' => fake()->company(),
        ];
    }
}
