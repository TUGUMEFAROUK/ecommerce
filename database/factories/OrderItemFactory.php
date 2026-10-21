<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * The product is created eagerly so the name, SKU and price snapshots
     * stored on the order item match the product being ordered.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->create();
        $quantity = fake()->numberBetween(1, 3);
        $unitPrice = $product->price;

        return [
            'order_id' => Order::factory(),
            'product_id' => $product->getKey(),
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'line_total' => number_format(((float) $unitPrice) * $quantity, 2, '.', ''),
        ];
    }
}
