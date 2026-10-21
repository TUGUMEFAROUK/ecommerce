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
