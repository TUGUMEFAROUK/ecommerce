<?php

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function checkoutPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '555-0100',
        'shipping_line1' => '123 Main Street',
        'shipping_line2' => 'Apartment 4',
        'shipping_city' => 'Springfield',
        'shipping_state' => 'Illinois',
        'shipping_postal_code' => '62704',
        'shipping_country' => 'US',
        'payment_method' => 'cod',
    ], $overrides);
}

test('guests are redirected to login when visiting checkout', function () {
    $this->get(route('checkout.show'))->assertRedirect(route('login'));

    $this->post(route('checkout.store'), checkoutPayload())
        ->assertRedirect(route('login'));
});

test('the checkout page shows contact, shipping, and the manual payment option', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 3]);

    $this->actingAs($user)
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Contact information', false)
        ->assertSee('Shipping address', false)
        ->assertSee('Cash on delivery', false)
        ->assertSee('$30.00', false)
        ->assertSee('$35.99', false)
        ->assertDontSee('name="total"', false)
        ->assertDontSee('name="price"', false);
});

test('checkout with an empty cart redirects back to the cart page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHas('status', 'Your cart is empty.');
});

test('posting checkout with an empty cart fails without creating an order', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('checkout.store'), checkoutPayload())
        ->assertInvalid(['cart' => 'Your cart is empty.']);

    $this->assertDatabaseCount('orders', 0);
});

test('a successful checkout creates the order, items, and a pending cod payment', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create([
        'price' => 12.50,
        'stock_quantity' => 10,
        'sku' => 'SKU-TEST-1',
    ]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 3]);

    $response = $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    $response->assertRedirect(route('checkout.confirmation', $order));

    expect($order->user_id)->toBe($user->id)
        ->and($order->status->value)->toBe('pending')
        ->and($order->total)->toBe('43.49')
        ->and($order->contact_name)->toBe('Jane Doe')
        ->and($order->contact_email)->toBe('jane@example.com')
        ->and($order->shipping_line1)->toBe('123 Main Street')
        ->and($order->shipping_postal_code)->toBe('62704');

    $item = $order->items()->sole();

    expect($item->product_id)->toBe($product->id)
        ->and($item->product_name)->toBe($product->name)
        ->and($item->product_sku)->toBe('SKU-TEST-1')
        ->and($item->unit_price)->toBe('12.50')
        ->and($item->quantity)->toBe(3)
        ->and($item->line_total)->toBe('37.50');

    $payment = $order->payments()->sole();

    expect($payment->provider)->toBe('cod')
        ->and($payment->status->value)->toBe('pending')
        ->and($payment->amount)->toBe('43.49')
        ->and($payment->paid_at)->toBeNull();

    expect($product->refresh()->stock_quantity)->toBe(7);
    $this->assertDatabaseCount('cart_items', 0);
});

test('the confirmation page shows the order number and manual payment wording', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    $this->actingAs($user)
        ->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertSee('Order number', false)
        ->assertSee('#'.$order->id, false)
        ->assertSee('Cash on delivery', false)
        ->assertSee('No online payment has been taken', false);
});

test('customers cannot view the confirmation of another customers order', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($owner)
        ->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($owner)
        ->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    $this->actingAs($intruder)
        ->get(route('checkout.confirmation', $order))
        ->assertNotFound();

    $this->actingAs($intruder)
        ->get(route('account.orders.show', $order))
        ->assertNotFound();
});

test('the confirmation page requires authentication', function () {
    $order = Order::factory()->create();

    $this->get(route('checkout.confirmation', $order))
        ->assertRedirect(route('login'));
});

test('totals posted by the browser are ignored and the server price wins', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload([
            'total' => '0.01',
            'subtotal' => '0.00',
            'grand_total' => '1.00',
            'price' => '0.01',
        ]))
        ->assertRedirect();

    $order = Order::query()->firstOrFail();

    expect($order->total)->toBe('25.99');
    expect($order->items()->sole()->unit_price)->toBe('10.00');
});

test('a price changed in the database is used at checkout, not the stale cart price', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $product->update(['price' => 15.00]);

    $this->actingAs($user)
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('$30.00', false);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload());

    $order = Order::query()->firstOrFail();

    expect($order->total)->toBe('35.99')
        ->and($order->items()->sole()->unit_price)->toBe('15.00')
        ->and($order->items()->sole()->line_total)->toBe('30.00');
});

test('a stock shortage at checkout is rejected and the cart stays intact', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 3]);

    $product->update(['stock_quantity' => 2]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload())
        ->assertInvalid(['cart' => "Only 2 of {$product->name} in stock."]);

    $this->assertDatabaseCount('orders', 0);
    expect($product->refresh()->stock_quantity)->toBe(2);

    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 3,
    ]);
});

test('a product that became unavailable blocks checkout', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1]);

    $product->update(['status' => 'draft']);

    $this->actingAs($user)
        ->get(route('checkout.show'))
        ->assertRedirect(route('cart.index'))
        ->assertSessionHasErrors(['cart' => "{$product->name} is no longer available."]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload())
        ->assertInvalid(['cart' => "{$product->name} is no longer available."]);

    $this->assertDatabaseCount('orders', 0);
    $this->assertDatabaseHas('cart_items', ['product_id' => $product->id]);
});

test('duplicate checkout submissions create only one order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload())
        ->assertRedirect();

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload())
        ->assertInvalid(['cart' => 'Your cart is empty.']);

    $this->assertDatabaseCount('orders', 1);
    expect($product->refresh()->stock_quantity)->toBe(9);
});

test('missing contact and shipping details are rejected', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('checkout.store'), [])
        ->assertInvalid([
            'contact_name',
            'contact_email',
            'contact_phone',
            'shipping_line1',
            'shipping_city',
            'shipping_state',
            'shipping_postal_code',
            'shipping_country',
            'payment_method',
        ]);

    $this->assertDatabaseCount('orders', 0);
});

test('only the configured cash on delivery option is accepted', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), checkoutPayload(['payment_method' => 'card']))
        ->assertInvalid(['payment_method' => 'The selected payment method is invalid.']);

    $this->assertDatabaseCount('orders', 0);
});
