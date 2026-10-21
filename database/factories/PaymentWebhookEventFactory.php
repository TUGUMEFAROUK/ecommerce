<?php

namespace Database\Factories;

use App\Models\PaymentWebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PaymentWebhookEvent>
 */
class PaymentWebhookEventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'provider' => 'flutterwave',
            'event_id' => fake()->unique()->uuid(),
            'event_type' => 'charge.completed',
            'processed_at' => now(),
        ];
    }
}
