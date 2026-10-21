<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('a product page shows its name description price stock and sku', function () {
    $product = Product::factory()->create([
        'name' => 'Aurora X2 Smartphone',
        'description' => 'A flagship phone with an all-day battery.',
        'price' => 699.00,
        'sku' => 'SKU-PHONE-0001',
        'stock_quantity' => 25,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Aurora X2 Smartphone', false)
        ->assertSee('A flagship phone with an all-day battery.', false)
        ->assertSee('$699.00', false)
        ->assertSee('SKU-PHONE-0001', false)
        ->assertSee('In stock (25 available)', false);
});

test('the product page falls back to a placeholder when image files are missing', function () {
    $product = Product::factory()->create();

    $product->images()->create([
        'path' => 'products/never-written.jpg',
        'position' => 0,
        'is_primary' => true,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('images/product-placeholder.svg', false);
});

test('the product page serves stored images from the public storage disk', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $product->images()->create([
        'path' => 'products/real-photo.jpg',
        'position' => 0,
        'is_primary' => true,
    ]);

    Storage::disk('public')->put('products/real-photo.jpg', 'fake-image-bytes');

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('products/real-photo.jpg', false)
        ->assertDontSee('product-placeholder.svg', false);
});

test('a product with no images still renders with a placeholder', function () {
    $product = Product::factory()->create();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('images/product-placeholder.svg', false)
        ->assertSee('no image available', false);
});

test('product descriptions are escaped as plain text', function () {
    $product = Product::factory()->create([
        'description' => 'Great <script>alert("xss")</script> product',
    ]);

    $response = $this->get(route('products.show', $product));

    $response->assertOk();
    expect($response->getContent())
        ->toContain('Great &lt;script&gt;')
        ->not->toContain('<script>alert("xss")</script>');
});

test('the product page shows an out of stock state with a disabled control', function () {
    $product = Product::factory()->create(['stock_quantity' => 0]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Out of stock', false)
        ->assertSee('aria-disabled="true"', false);
});

test('guests can add products to their cart from the product page', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('Add to cart', false)
        ->assertSee('action="'.route('products.cart.add', $product).'"', false);
});

test('the quantity control is bounded by stock and the per-item cap', function () {
    $product = Product::factory()->create(['stock_quantity' => 4]);

    $this->actingAs(User::factory()->create())
        ->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('max="4"', false);
});

test('draft products return a not found response', function () {
    $product = Product::factory()->draft()->create();

    $this->get(route('products.show', $product))->assertNotFound();
});

test('archived products return a not found response', function () {
    $product = Product::factory()->archived()->create();

    $this->get(route('products.show', $product))->assertNotFound();
});

test('an invalid product slug returns the custom not found page', function () {
    $this->get('/products/this-slug-does-not-exist')
        ->assertNotFound()
        ->assertSee('We could not find that page', false);
});

test('any unknown url returns the custom not found page', function () {
    $this->get('/definitely/not/a/route')
        ->assertNotFound()
        ->assertSee('We could not find that page', false)
        ->assertSee('Browse products', false);
});

test('the product page has a labelled quantity input', function () {
    $product = Product::factory()->create();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('for="quantity"', false)
        ->assertSee('name="quantity"', false);
});
