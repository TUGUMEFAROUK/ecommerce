<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * The checkout details shared by the order tests below.
 *
 * @return array<string, string>
 */
function storefrontCheckoutPayload(): array
{
    return [
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
    ];
}

test('the announcement bar is shown by default and quotes the free-delivery threshold', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Complimentary delivery on orders over $75.00', false);
});

test('the announcement bar can be switched off from the settings', function () {
    app(SettingsService::class)->set(['announcement_enabled' => '0']);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Complimentary delivery on orders over', false);
});

test('a custom announcement message and link are rendered', function () {
    app(SettingsService::class)->set([
        'announcement_text' => 'Signature pieces, limited numbers.',
        'announcement_link' => '/products',
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Signature pieces, limited numbers.', false)
        ->assertSee('Shop now', false)
        ->assertSee(url('/products'), false);
});

test('shipping, returns, payments, privacy and terms policies are published', function (string $policy, string $heading) {
    $this->get(route('policies.show', $policy))
        ->assertOk()
        ->assertSee($heading, false);
})->with([
    ['shipping', 'Delivery charges'],
    ['returns', 'Return window'],
    ['payments', 'Accepted methods'],
    ['privacy', 'What we store'],
    ['terms', 'Orders and pricing'],
]);

test('the returns policy quotes the configured return window', function () {
    app(SettingsService::class)->set(['returns_window_days' => '45']);

    $this->get(route('policies.show', 'returns'))
        ->assertOk()
        ->assertSee('within 45 days of delivery', false);
});

test('the shipping policy quotes the configured delivery fee and threshold', function () {
    app(SettingsService::class)->set([
        'shipping_flat_fee' => '8.50',
        'shipping_free_threshold' => '120.00',
    ]);

    $this->get(route('policies.show', 'shipping'))
        ->assertOk()
        ->assertSee('$8.50', false)
        ->assertSee('$120.00', false);
});

test('an unknown policy page returns a 404', function () {
    $this->get(route('policies.show', 'nope'))->assertNotFound();
});

test('the footer links to every published policy', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('policies.show', 'shipping'), false)
        ->assertSee(route('policies.show', 'returns'), false)
        ->assertSee(route('policies.show', 'privacy'), false)
        ->assertSee(route('policies.show', 'terms'), false);
});

test('a discounted product shows its sale price and discount on the catalog', function () {
    $product = Product::factory()->create([
        'name' => 'Linen Throw',
        'price' => 100.00,
        'sale_price' => 75.00,
        'stock_quantity' => 5,
    ]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Linen Throw', false)
        ->assertSee('$75.00', false)
        ->assertSee('$100.00', false)
        ->assertSee('&minus;25%', false);
});

test('a discounted product shows its sale price on the product page', function () {
    $product = Product::factory()->create([
        'name' => 'Linen Throw',
        'price' => 100.00,
        'sale_price' => 75.00,
        'stock_quantity' => 5,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('$75.00', false)
        ->assertSee('$100.00', false)
        ->assertSee('Save 25%', false);
});

test('a sale price is ignored when it is not lower than the list price', function () {
    $product = Product::factory()->create([
        'name' => 'Full Price Piece',
        'price' => 80.00,
        'sale_price' => 90.00,
        'stock_quantity' => 5,
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertDontSee('Save', false)
        ->assertSee('$80.00', false);
});

test('the cart charges the sale price rather than the list price', function () {
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'price' => 100.00,
        'sale_price' => 60.00,
        'stock_quantity' => 10,
    ]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->get(route('cart.index'))
        ->assertOk()
        ->assertSee('$60.00', false)
        ->assertDontSee('$100.00', false);
});

test('a checkout records the sale price on the order item', function () {
    $user = User::factory()->create();

    $product = Product::factory()->create([
        'price' => 100.00,
        'sale_price' => 60.00,
        'stock_quantity' => 10,
    ]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), storefrontCheckoutPayload())
        ->assertRedirect();

    $order = Order::query()->sole();
    $item = $order->items()->sole();

    expect($order->total)->toBe('65.99')
        ->and($item->unit_price)->toBe('60.00')
        ->and($item->line_total)->toBe('60.00');
});

test('a category without an image falls back to its first product photo', function () {
    Storage::fake('public');

    $category = Category::factory()->create(['name' => 'Textiles']);
    $product = Product::factory()->for($category)->create(['stock_quantity' => 5]);

    Storage::disk('public')->put('products/linen.jpg', 'fake-image');

    $product->images()->create([
        'path' => 'products/linen.jpg',
        'position' => 0,
    ]);

    $response = $this->get(route('home'))->assertOk();
    $content = $response->getContent();

    $categorySection = substr(
        $content,
        strpos($content, 'id="categories-heading"'),
        strpos($content, 'id="featured-heading"') - strpos($content, 'id="categories-heading"'),
    );

    expect($categorySection)
        ->toContain('products/linen.jpg')
        ->toContain('Textiles collection');
});

test('a category image takes priority over its product photos', function () {
    Storage::fake('public');

    $category = Category::factory()->create([
        'name' => 'Textiles',
        'image' => 'categories/textiles.jpg',
    ]);
    $product = Product::factory()->for($category)->create(['stock_quantity' => 5]);

    Storage::disk('public')->put('categories/textiles.jpg', 'fake-image');
    Storage::disk('public')->put('products/linen.jpg', 'fake-image');

    $product->images()->create([
        'path' => 'products/linen.jpg',
        'position' => 0,
    ]);

    $response = $this->get(route('home'))->assertOk();
    $content = $response->getContent();

    $categorySection = substr(
        $content,
        strpos($content, 'id="categories-heading"'),
        strpos($content, 'id="featured-heading"') - strpos($content, 'id="categories-heading"'),
    );

    expect($categorySection)
        ->toContain('categories/textiles.jpg')
        ->not->toContain('products/linen.jpg');
});
