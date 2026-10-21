<?php

use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;

test('the inventory list shows active products at or below the configured threshold', function () {
    app(SettingsService::class)->set(['low_stock_threshold' => '5']);

    Product::factory()->create(['name' => 'Running Low', 'stock_quantity' => 5, 'status' => 'active']);
    Product::factory()->create(['name' => 'Comfortably Stocked', 'stock_quantity' => 50, 'status' => 'active']);
    Product::factory()->create(['name' => 'Retired Draft', 'stock_quantity' => 2, 'status' => 'draft']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.inventory.index'))
        ->assertOk()
        ->assertSee('Running Low', false)
        ->assertDontSee('Comfortably Stocked', false)
        ->assertDontSee('Retired Draft', false)
        ->assertSee('5', false);
});

test('the inventory list shows the recent movement log', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.inventory.index'))
        ->assertOk();

    $product->refresh();
    expect($product)->not->toBeNull();
});

test('adjusting stock records an auditable movement with the acting admin', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.inventory.update', $product), [
            'stock_quantity' => 25,
            'reason' => 'New shipment',
        ])
        ->assertRedirect();

    expect($product->refresh()->stock_quantity)->toBe(25);

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->getKey(),
        'user_id' => $admin->getKey(),
        'change' => 15,
        'stock_after' => 25,
        'reason' => 'New shipment',
    ]);
});

test('reducing stock records a negative movement', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.inventory.update', $product), ['stock_quantity' => 4])
        ->assertRedirect();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->getKey(),
        'change' => -6,
        'stock_after' => 4,
        'reason' => null,
    ]);
});

test('adjusting stock to the same level records no movement', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('admin.inventory.update', $product), ['stock_quantity' => 10])
        ->assertRedirect();

    $this->assertDatabaseCount('stock_movements', 0);
    expect($product->refresh()->stock_quantity)->toBe(10);
});

test('the stock adjustment validates the quantity', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.inventory.update', $product), ['stock_quantity' => -5])
        ->assertInvalid(['stock_quantity']);

    $this->actingAs($admin)
        ->patch(route('admin.inventory.update', $product), ['stock_quantity' => 'lots'])
        ->assertInvalid(['stock_quantity']);

    expect($product->refresh()->stock_quantity)->toBe(10);
    $this->assertDatabaseCount('stock_movements', 0);
});

test('customers are denied access to the inventory routes', function (string $method, string $routeName) {
    $product = Product::factory()->create(['stock_quantity' => 10]);
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'patch' ? ['stock_quantity' => 99] : [];

    $this->actingAs($customer)
        ->call($method, route($routeName, [$product]), $payload)
        ->assertForbidden();

    expect($product->refresh()->stock_quantity)->toBe(10);
})->with([
    ['get', 'admin.inventory.index'],
    ['patch', 'admin.inventory.update'],
]);

test('guests are redirected to login from the inventory routes', function () {
    $product = Product::factory()->create(['stock_quantity' => 42]);

    $this->get(route('admin.inventory.index'))->assertRedirect(route('login'));
    $this->patch(route('admin.inventory.update', $product), ['stock_quantity' => 1])
        ->assertRedirect(route('login'));

    expect($product->refresh()->stock_quantity)->toBe(42);
    $this->assertDatabaseCount('stock_movements', 0);
});
