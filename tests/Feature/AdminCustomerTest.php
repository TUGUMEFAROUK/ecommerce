<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;

test('the customer list shows only customer accounts, never admin accounts', function () {
    $customer = User::factory()->create(['name' => 'Katherine Johnson', 'email' => 'katherine@example.com']);
    User::factory()->create(['name' => 'Hidden Admin', 'role' => UserRole::Admin]);
    User::factory()->create();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.customers.index'))
        ->assertOk()
        ->assertSee('Katherine Johnson', false)
        ->assertSee('katherine@example.com', false)
        ->assertDontSee('Hidden Admin', false);
});

test('the customer list can be searched by name or email', function () {
    User::factory()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
    User::factory()->create(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $this->actingAs($admin)->get(route('admin.customers.index', ['search' => 'ada@']))
        ->assertOk()
        ->assertSee('Ada Lovelace', false)
        ->assertDontSee('Grace Hopper', false);

    $this->actingAs($admin)->get(route('admin.customers.index', ['search' => 'hopper']))
        ->assertOk()
        ->assertSee('Grace Hopper', false)
        ->assertDontSee('Ada Lovelace', false);
});

test('the customer page counts only recognized statuses towards total spent', function () {
    $customer = User::factory()->create();

    Order::factory()->for($customer)->paid()->create(['total' => 100.00]);
    Order::factory()->for($customer)->create(['status' => OrderStatus::Processing, 'total' => 40.00]);
    Order::factory()->for($customer)->create(['total' => 50.00]);
    Order::factory()->for($customer)->cancelled()->create(['total' => 30.00]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.customers.show', $customer))
        ->assertOk()
        ->assertSee('USD 140.00', false)
        ->assertSee('#', false);
});

test('the customer page reports customers with no orders', function () {
    $customer = User::factory()->create();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.customers.show', $customer))
        ->assertOk()
        ->assertSee('This customer has not placed any orders yet.', false)
        ->assertSee('USD 0.00', false);
});

test('admin accounts cannot be opened through the customer routes', function () {
    $adminTarget = User::factory()->create(['name' => 'Secret Admin', 'role' => UserRole::Admin]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.customers.show', $adminTarget))
        ->assertNotFound()
        ->assertDontSee('Secret Admin', false);
});

test('customers cannot escalate their own role through the profile form', function () {
    $customer = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($customer)
        ->patch(route('account.update'), [
            'name' => $customer->name,
            'email' => $customer->email,
            'role' => 'admin',
        ])
        ->assertRedirect(route('account.show'));

    expect($customer->refresh()->role)->toBe(UserRole::Customer);
});

test('customers are denied access to the admin customer routes', function (string $method, string $routeName) {
    $customer = User::factory()->create(['role' => UserRole::Customer]);
    $other = User::factory()->create(['role' => UserRole::Customer]);

    $parameters = $routeName === 'admin.customers.index' ? [] : [$other];

    $this->actingAs($customer)
        ->call($method, route($routeName, $parameters))
        ->assertForbidden();
})->with([
    ['get', 'admin.customers.index'],
    ['get', 'admin.customers.show'],
]);

test('guests are redirected to login from the customer routes', function () {
    $customer = User::factory()->create();

    $this->get(route('admin.customers.index'))->assertRedirect(route('login'));
    $this->get(route('admin.customers.show', $customer))->assertRedirect(route('login'));
});
