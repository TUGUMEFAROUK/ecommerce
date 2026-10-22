<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\StoreStatisticsService;

test('the dashboard sales totals include recognized statuses and exclude pending and cancelled', function () {
    Order::factory()->paid()->create(['total' => 100.00]);
    Order::factory()->create(['status' => OrderStatus::Processing, 'total' => 50.00]);
    Order::factory()->create(['total' => 25.00]);
    Order::factory()->cancelled()->create(['total' => 40.00]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('USD 150.00', false);
});

test('month sales only count orders created in the current month', function () {
    Order::factory()->paid()->create(['total' => 100.00, 'created_at' => now()->subMonth()]);
    Order::factory()->paid()->create(['total' => 20.00]);

    $summary = app(StoreStatisticsService::class)->summary();

    expect($summary['total_sales'])->toBe(120.00)
        ->and($summary['month_sales'])->toBe(20.00);
});

test('orders are counted per status including statuses with no orders', function () {
    Order::factory()->create();
    Order::factory()->paid()->create();
    Order::factory()->paid()->create();

    $counts = app(StoreStatisticsService::class)->summary()['orders_by_status'];

    expect($counts['pending'])->toBe(1)
        ->and($counts['paid'])->toBe(2)
        ->and($counts['cancelled'])->toBe(0)
        ->and($counts['refunded'])->toBe(0);
});

test('the low stock banner counts only active products at or below the threshold', function () {
    Product::factory()->create(['stock_quantity' => 3, 'status' => 'active']);
    Product::factory()->create(['stock_quantity' => 50, 'status' => 'active']);
    Product::factory()->create(['stock_quantity' => 2, 'status' => 'draft']);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('1 product at or below the low-stock threshold.', false);
});

test('customer counts include only customer accounts', function () {
    User::factory()->create();
    User::factory()->create();
    User::factory()->create(['role' => UserRole::Admin]);

    expect(app(StoreStatisticsService::class)->summary()['customers'])->toBe(2);
});

test('the dashboard lists recent orders with their customer', function () {
    $owner = User::factory()->create(['name' => 'Ada Lovelace']);
    $order = Order::factory()->for($owner)->create();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('#'.$order->id, false)
        ->assertSee('Ada Lovelace', false);
});

test('the sales series covers every day of the selected range', function () {
    Order::factory()->paid()->create(['total' => 40.00]);

    $series = app(StoreStatisticsService::class)->salesSeries(7);

    expect($series)->toHaveCount(7)
        ->and($series[0]['date'])->toBe(now()->subDays(6)->format('Y-m-d'))
        ->and($series[6]['date'])->toBe(now()->format('Y-m-d'))
        ->and(array_sum(array_column($series, 'total')))->toBe(40.00);
});

test('the sales series ignores orders that are not recognized sales', function () {
    Order::factory()->paid()->create(['total' => 40.00]);
    Order::factory()->create(['total' => 500.00]);
    Order::factory()->cancelled()->create(['total' => 700.00]);

    $series = app(StoreStatisticsService::class)->salesSeries(30);

    expect(array_sum(array_column($series, 'total')))->toBe(40.00);
});

test('the dashboard chart range defaults to thirty days and ignores unknown ranges', function () {
    Order::factory()->paid()->create(['total' => 40.00]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard', ['range' => 7]))
        ->assertOk()
        ->assertSee('in the last 7 days', false)
        ->assertSee('30d', false);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard', ['range' => 999]))
        ->assertOk()
        ->assertSee('in the last 30 days', false);
});

test('the dashboard lists the best selling products from recognized sales', function () {
    $product = Product::factory()->create(['name' => 'Handwoven Throw']);

    Order::factory()->paid()->create()->items()->create([
        'product_id' => $product->getKey(),
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'unit_price' => 25.00,
        'quantity' => 4,
        'line_total' => 100.00,
    ]);

    Order::factory()->create()->items()->create([
        'product_id' => $product->getKey(),
        'product_name' => $product->name,
        'product_sku' => $product->sku,
        'unit_price' => 25.00,
        'quantity' => 9,
        'line_total' => 225.00,
    ]);

    $top = app(StoreStatisticsService::class)->topSellingProducts();

    expect($top)->toHaveCount(1)
        ->and($top[0]['name'])->toBe('Handwoven Throw')
        ->and($top[0]['units'])->toBe(4)
        ->and($top[0]['revenue'])->toBe(100.00);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Handwoven Throw', false)
        ->assertSee('4 units sold', false);
});
