<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests can add products to a session cart without an account', function () {
    $product = Product::factory()->create();

    $this->post(route('products.cart.add', $product), ['quantity' => 2])
        ->assertRedirect();

    expect(session('guest_cart'))
        ->toBeArray()
        ->toHaveKey($product->id, 2);

    $this->assertDatabaseCount('carts', 0);
});

test('an authenticated customer can add a product to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2])
        ->assertRedirect();

    $this->assertDatabaseHas('carts', ['user_id' => $user->id]);
    $this->assertDatabaseHas('cart_items', [
        'product_id' => $product->id,
        'quantity' => 2,
    ]);
});

test('adding the same product again increments its quantity', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 3]);

    $cart = Cart::query()->where('user_id', $user->id)->firstOrFail();

    expect($cart->items()->where('product_id', $product->id)->first()->quantity)->toBe(5);
});

test('the combined quantity never exceeds the available stock or the per-item cap', function () {
    $user = User::factory()->create();

    $limitedStock = Product::factory()->create(['stock_quantity' => 5]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $limitedStock), ['quantity' => 6])
        ->assertInvalid(['quantity' => 'The quantity field must not be greater than 5.']);

    $plentyStock = Product::factory()->create(['stock_quantity' => 100]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $plentyStock), ['quantity' => 11])
        ->assertInvalid(['quantity' => 'The quantity field must not be greater than 10.']);
});

test('a quantity of zero is rejected', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 0])
        ->assertInvalid('quantity');
});

test('out of stock products cannot be added to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 0]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1])
        ->assertInvalid(['quantity' => 'This product is out of stock.']);

    $this->assertDatabaseMissing('cart_items', ['product_id' => $product->id]);
});

test('draft products cannot be added to the cart', function () {
    $user = User::factory()->create();
    $product = Product::factory()->draft()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->post(route('products.cart.add', $product), ['quantity' => 1])
        ->assertNotFound();
});

test('each customer gets their own cart', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($first)->post(route('products.cart.add', $product), ['quantity' => 1]);
    $this->actingAs($second)->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->assertDatabaseCount('carts', 2);

    $this->assertDatabaseHas('cart_items', ['cart_id' => Cart::where('user_id', $first->id)->firstOrFail()->id, 'quantity' => 1]);
    $this->assertDatabaseHas('cart_items', ['cart_id' => Cart::where('user_id', $second->id)->firstOrFail()->id, 'quantity' => 2]);
});

test('the add to cart form posts to the cart route from the product page', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('action="'.route('products.cart.add', $product).'"', false)
        ->assertSee('Add to cart', false);
});
