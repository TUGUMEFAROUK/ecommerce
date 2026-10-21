<?php

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;

function adminOrderStatusPayload(string $status, array $overrides = []): array
{
    return array_merge(['status' => $status], $overrides);
}

test('the order index lists orders and filters by search and status', function () {
    $owner = User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
    $order = Order::factory()->for($owner)->create();
    Order::factory()->paid()->create();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.orders.index'))
        ->assertOk()
        ->assertSee('#'.$order->id, false);

    $this->actingAs($admin)->get(route('admin.orders.index', ['search' => 'grace@example.com']))
        ->assertOk()
        ->assertSee('#'.$order->id, false);

    $this->actingAs($admin)->get(route('admin.orders.index', ['search' => (string) ($order->id + 900)]))
        ->assertOk()
        ->assertDontSee('#'.$order->id, false);

    $this->actingAs($admin)->get(route('admin.orders.index', ['status' => 'paid']))
        ->assertOk()
        ->assertSee('paid', false);
});

test('an administrator can view any order with its items, payment, and history', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('#'.$order->id, false);
});

test('guests are redirected to login from the admin order routes', function () {
    $order = Order::factory()->create();

    $this->get(route('admin.orders.index'))->assertRedirect(route('login'));
    $this->get(route('admin.orders.show', $order))->assertRedirect(route('login'));
    $this->patch(route('admin.orders.status', $order), ['status' => 'paid'])
        ->assertRedirect(route('login'));

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
});

test('a valid transition moves the order and records history with the acting admin', function () {
    $order = Order::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('processing', ['notes' => 'Packed today']))
        ->assertRedirect(route('admin.orders.show', $order));

    expect($order->refresh()->status)->toBe(OrderStatus::Processing);

    $this->assertDatabaseHas('order_status_history', [
        'order_id' => $order->getKey(),
        'user_id' => $admin->getKey(),
        'from_status' => 'pending',
        'to_status' => 'processing',
        'notes' => 'Packed today',
    ]);
});

test('an illegal transition is rejected and leaves the order untouched', function () {
    $order = Order::factory()->create(['status' => OrderStatus::Delivered]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('pending'))
        ->assertInvalid(['status' => 'An order cannot move from Delivered to Pending.']);

    expect($order->refresh()->status)->toBe(OrderStatus::Delivered);
    $this->assertDatabaseCount('order_status_history', 0);
});

test('moving an order to its current status is rejected', function () {
    $order = Order::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('pending'))
        ->assertInvalid(['status' => 'The order is already Pending.']);

    $this->assertDatabaseCount('order_status_history', 0);
});

test('terminal orders allow no further transitions', function () {
    $order = Order::factory()->cancelled()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('paid'))
        ->assertInvalid(['status' => 'An order cannot move from Cancelled to Paid.']);

    expect($order->refresh()->status)->toBe(OrderStatus::Cancelled);
    $this->assertDatabaseCount('order_status_history', 0);
});

test('the status field is required and must be a valid status', function () {
    $order = Order::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), [])
        ->assertInvalid(['status' => 'The status field is required.']);

    $this->actingAs($admin)
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('archived'))
        ->assertInvalid(['status' => 'The selected status is invalid.']);
});

test('customers cannot change order statuses', function () {
    $order = Order::factory()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->patch(route('admin.orders.status', $order), adminOrderStatusPayload('processing'))
        ->assertForbidden();

    expect($order->refresh()->status)->toBe(OrderStatus::Pending);
    $this->assertDatabaseCount('order_status_history', 0);
});

test('customers are denied access to the admin order routes', function (string $method, string $routeName) {
    $order = Order::factory()->create();
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'get' ? [] : adminOrderStatusPayload('processing');

    $this->actingAs($customer)
        ->call($method, route($routeName, [$order]), $payload)
        ->assertForbidden();
})->with([
    ['get', 'admin.orders.index'],
    ['get', 'admin.orders.show'],
    ['patch', 'admin.orders.status'],
]);
