<?php

namespace Database\Factories;

use App\Enums\AddressType;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => AddressType::Shipping,
            'recipient_name' => fake()->name(),
            'line1' => fake()->streetAddress(),
            'line2' => fake()->secondaryAddress(),
            'city' => fake()->city(),
            'state' => fake()->stateAbbr(),
            'postal_code' => fake()->postcode(),
            'country' => fake()->countryCode(),
            'phone' => fake()->phoneNumber(),
            'is_default' => false,
        ];
    }

    /**
     * Indicate that the address is a billing address.
     */
    public function billing(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AddressType::Billing,
        ]);
    }
}
