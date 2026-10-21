<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The order is created eagerly so the payment amount and currency match
     * the order being paid.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $order = Order::factory()->create();

        return [
            'order_id' => $order->getKey(),
            'provider' => fake()->randomElement(['stripe', 'paypal']),
            'provider_reference' => strtoupper(fake()->unique()->bothify('REF-########??')),
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => PaymentStatus::Pending,
            'paid_at' => null,
        ];
    }

    /**
     * Indicate that the payment succeeded.
     */
    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Succeeded,
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Failed,
        ]);
    }
}
