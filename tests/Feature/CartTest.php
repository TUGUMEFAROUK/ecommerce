<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can view their session cart with server-computed totals', function () {
    $product = Product::factory()->create(['price' => 12.50, 'stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee($product->name)
        ->assertSee('$25.00', false)
        ->assertSee('$5.99', false)
        ->assertSee('$30.99', false);
});

test('the cart page shows the current database price, never a stale one', function () {
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 2]);

    $product->update(['price' => 15.00]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('$30.00', false);
});

test('shipping is free once the subtotal reaches the threshold', function () {
    $product = Product::factory()->create(['price' => 80.00, 'stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Free', false)
        ->assertSee('$80.00', false);
});

test('discounts are always computed server side and currently zero', function () {
    $product = Product::factory()->create(['price' => 20.00, 'stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Discount', false)
        ->assertSee('$0.00', false);
});

test('an authenticated customer can update the quantity of a cart line', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 5])
        ->assertRedirect();

    $quantity = Cart::query()
        ->where('user_id', $user->id)
        ->firstOrFail()
        ->items()
        ->where('product_id', $product->id)
        ->first()
        ->quantity;

    expect($quantity)->toBe(5);
});

test('a guest can update the quantity in their session cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->patch(route('cart.update', $product), ['quantity' => 4])
        ->assertRedirect();

    expect(session('guest_cart'))
        ->toBeArray()
        ->toHaveKey($product->id, 4);
});

test('updating a quantity beyond the available stock is rejected', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 5]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 6])
        ->assertInvalid(['quantity' => 'The quantity field must not be greater than 5.']);

    $quantity = Cart::query()
        ->where('user_id', $user->id)
        ->firstOrFail()
        ->items()
        ->where('product_id', $product->id)
        ->first()
        ->quantity;

    expect($quantity)->toBe(2);
});

test('a quantity of zero is rejected when updating', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 0])
        ->assertInvalid('quantity');
});

test('a non-integer quantity is rejected when updating', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 'two'])
        ->assertInvalid('quantity');
});

test('updating an out of stock product is rejected with a clear message', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $product->update(['stock_quantity' => 0]);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 1])
        ->assertInvalid(['quantity' => 'This product is out of stock.']);
});

test('inactive products cannot be quantity-updated', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $product->update(['status' => 'draft']);

    $this->actingAs($user)
        ->patch(route('cart.update', $product), ['quantity' => 1])
        ->assertNotFound();
});

test('updating a cart line for an unknown product returns 404', function () {
    $this->actingAs(User::factory()->create())
        ->patch('/cart/items/definitely-not-a-product', ['quantity' => 1])
        ->assertNotFound();
});

test('an authenticated customer can remove a line from the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->delete(route('cart.destroy', $product))
        ->assertRedirect();

    $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Your cart is empty', false);
});

test('a guest can remove a line from their session cart', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->delete(route('cart.destroy', $product))->assertRedirect();

    expect(session('guest_cart'))->toBe([]);
});

test('a customer can clear the entire cart', function () {
    $user = User::factory()->create();
    $first = Product::factory()->create(['stock_quantity' => 10]);
    $second = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)->post(route('products.cart.add', $first), ['quantity' => 1]);
    $this->actingAs($user)->post(route('products.cart.add', $second), ['quantity' => 3]);

    $this->actingAs($user)
        ->delete(route('cart.clear'))
        ->assertRedirect(route('cart.index'));

    $this->assertDatabaseCount('cart_items', 0);
});

test('a guest cart survives logging in and is persisted to the database', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->post(route('products.cart.add', $product), ['quantity' => 3]);

    $this->post('login', ['email' => $user->email, 'password' => 'password'])
        ->assertRedirect();

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee($product->name);

    $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();

    expect($cart->items()->where('product_id', $product->id)->first()->quantity)->toBe(3);
    expect(session()->has('guest_cart'))->toBeFalse();
});

test('the header shows a cart link with the current item count', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 3]);

    $content = $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('cart.index'), false)
        ->getContent();

    expect($content)->toMatch('/text-white">\s+3\s+<\/span>/');
});

test('cart lines are isolated between customers', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($first)->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($second)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('Your cart is empty', false);

    $this->assertDatabaseCount('carts', 1);
});
