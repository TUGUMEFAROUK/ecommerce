<?php

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/**
 * Write real files to the fake public disk and attach them to a product.
 */
function attachGalleryImages(Product $product, int $count): void
{
    $bytes = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    );

    for ($index = 0; $index < $count; $index++) {
        $path = "products/gallery-{$index}.png";

        Storage::disk('public')->put($path, $bytes);

        $product->images()->create([
            'path' => $path,
            'position' => $index,
            'is_primary' => $index === 0,
        ]);
    }
}

/**
 * Slice a rendered page down to the section that owns a heading id.
 */
function productSection(string $html, string $headingId): string
{
    $start = strpos($html, 'id="'.$headingId.'"');

    if ($start === false) {
        return '';
    }

    $end = strpos($html, '</section>', $start);

    return substr($html, $start, $end === false ? null : $end - $start);
}

test('the product page renders a thumbnail gallery with accessible zoom controls', function () {
    Storage::fake('public');

    $product = Product::factory()->create(['name' => 'Aurora Table Lamp']);
    attachGalleryImages($product, 3);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response
        ->assertSee('aria-label="Product images"', false)
        ->assertSee('data-gallery-thumb', false)
        ->assertSee('data-gallery-dialog', false)
        ->assertSee('data-gallery-open', false)
        ->assertSee('data-gallery-close', false)
        ->assertSee('data-gallery-prev', false)
        ->assertSee('data-gallery-next', false)
        ->assertSee('aria-label="Show image 2 of 3"', false)
        ->assertSee('aria-label="Enlarge image 1 of 3"', false)
        ->assertSee('fetchpriority="high"', false)
        ->assertSee('loading="lazy"', false);

    $content = $response->getContent();

    expect(substr_count($content, 'data-gallery-thumb'))->toBe(3)
        ->and(substr_count($content, 'fetchpriority="high"'))->toBe(1);
});

test('the gallery renders a single zoomable slide without thumbnails when one image exists', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    attachGalleryImages($product, 1);

    $content = $this->get(route('products.show', $product))->assertOk()->getContent();

    expect(substr_count($content, 'data-gallery-thumb'))->toBe(0)
        ->and($content)->toContain('data-gallery-dialog');
});

test('the product breadcrumb links back through its category', function () {
    $category = Category::factory()->create(['name' => 'Lighting', 'slug' => 'lighting']);
    $product = Product::factory()->create([
        'name' => 'Arc Floor Lamp',
        'category_id' => $category->getKey(),
    ]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('aria-label="Breadcrumb"', false)
        ->assertSee(route('products.index', ['category' => 'lighting']), false)
        ->assertSee('Lighting', false)
        ->assertSee('aria-current="page"', false);
});

test('related products come from the same category and are limited to four', function () {
    $category = Category::factory()->create();
    $otherCategory = Category::factory()->create();

    $product = Product::factory()->create(['category_id' => $category->getKey()]);

    $names = [
        'Related One' => 90,
        'Related Two' => 80,
        'Related Three' => 70,
        'Related Four' => 60,
        'Related Five' => 50,
        'Related Six' => 40,
    ];

    foreach ($names as $name => $stock) {
        Product::factory()->create([
            'name' => $name,
            'slug' => strtolower(str_replace(' ', '-', $name)),
            'category_id' => $category->getKey(),
            'stock_quantity' => $stock,
        ]);
    }

    Product::factory()->create(['name' => 'Unrelated Item', 'category_id' => $otherCategory->getKey()]);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response
        ->assertSee('id="related-heading"', false)
        ->assertSee('View all '.$category->name, false)
        ->assertSee('Related One', false)
        ->assertSee('Related Four', false)
        ->assertDontSee('Related Five', false)
        ->assertDontSee('Related Six', false)
        ->assertDontSee('Unrelated Item', false);

    $section = productSection($response->getContent(), 'related-heading');

    expect(substr_count($section, 'data-product-card'))->toBe(4);
});

test('recently viewed products appear on later visits and never include the current product', function () {
    $first = Product::factory()->create(['name' => 'First Viewed Product']);
    $second = Product::factory()->create(['name' => 'Second Viewed Product']);

    $this->get(route('products.show', $first))->assertOk();

    $response = $this->get(route('products.show', $second))->assertOk();

    $response
        ->assertSee('id="recently-viewed-heading"', false)
        ->assertSee('First Viewed Product', false);

    $section = productSection($response->getContent(), 'recently-viewed-heading');

    expect($section)
        ->toContain('First Viewed Product')
        ->not->toContain('Second Viewed Product')
        ->and(substr_count($section, 'data-product-card'))->toBe(1);
});

test('the recently viewed history stores identifiers only and skips unpublished products', function () {
    $published = Product::factory()->create();
    $draft = Product::factory()->draft()->create();

    $this->get(route('products.show', $published))->assertOk();
    $this->get(route('products.show', $draft))->assertNotFound();

    $history = session('recently_viewed_products');

    expect($history)->toBe([$published->getKey()])
        ->and(collect($history)->every(fn ($id): bool => is_int($id)))->toBeTrue();
});

test('the recently viewed history keeps the eight most recent products', function () {
    $products = Product::factory()->count(9)->create();

    foreach ($products as $product) {
        $this->get(route('products.show', $product))->assertOk();
    }

    $history = session('recently_viewed_products');

    expect($history)->toHaveCount(8)
        ->and($history[0])->toBe($products->last()->getKey())
        ->and($history)->not->toContain($products->first()->getKey());
});

test('shipping payment and returns details come from the store settings and policies', function () {
    app(SettingsService::class)->set([
        'delivery_estimate' => '3–6 business days',
        'shipping_flat_fee' => '7.50',
        'shipping_free_threshold' => '50.00',
        'returns_window_days' => '14',
    ]);

    $product = Product::factory()->create();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('3–6 business days', false)
        ->assertSee('$7.50', false)
        ->assertSee('$50.00', false)
        ->assertSee('14-day returns', false)
        ->assertSee(route('policies.show', 'shipping'), false)
        ->assertSee(route('policies.show', 'payments'), false)
        ->assertSee(route('policies.show', 'returns'), false);
});

test('the product page renders expandable description and specification panels', function () {
    $product = Product::factory()->create([
        'name' => 'Clay Vase',
        'description' => "Hand-thrown stoneware.\nFinished in a matte glaze.",
        'sku' => 'SKU-VASE-0001',
        'stock_quantity' => 8,
    ]);

    $response = $this->get(route('products.show', $product))->assertOk();

    $response
        ->assertSee('<details', false)
        ->assertSee('<summary', false)
        ->assertSee('Description', false)
        ->assertSee('Specifications', false)
        ->assertSee('SKU-VASE-0001', false)
        ->assertSee('Hand-thrown stoneware.', false)
        ->assertSee('Finished in a matte glaze.', false);
});

test('the purchase form exposes stock-aware quantity controls and async states', function () {
    $product = Product::factory()->create(['stock_quantity' => 4]);

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('data-add-to-cart', false)
        ->assertSee('data-add-to-cart-button', false)
        ->assertSee('data-add-to-cart-label', false)
        ->assertSee('data-add-to-cart-status', false)
        ->assertSee('data-add-to-cart-spinner', false)
        ->assertSee('data-qty-step="-1"', false)
        ->assertSee('data-qty-step="1"', false)
        ->assertSee('aria-controls="quantity"', false)
        ->assertSee('max="4"', false)
        ->assertSee('Only 4 available.', false);
});

test('the product page renders its suggestion rails with a fixed number of queries', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->getKey()]);

    Product::factory()->count(6)->create(['category_id' => $category->getKey()]);

    $previous = Product::factory()->create();

    $this->get(route('products.show', $previous))->assertOk();

    DB::enableQueryLog();

    $this->get(route('products.show', $product))
        ->assertOk()
        ->assertSee('id="related-heading"', false)
        ->assertSee('id="recently-viewed-heading"', false);

    $queryCount = count(DB::getQueryLog());

    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(20);
});

test('the header exposes a cart count marker for live updates', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('data-cart-count', false);
});

test('guests adding to the cart over json receive the updated count', function () {
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->postJson(route('products.cart.add', $product), ['quantity' => 2])
        ->assertOk()
        ->assertJson([
            'message' => 'Added to cart.',
            'count' => 2,
        ]);

    expect(session('guest_cart'))->toBe([$product->getKey() => 2]);
});

test('authenticated customers receive their own cart count over json', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['stock_quantity' => 10]);

    $this->actingAs($user)
        ->postJson(route('products.cart.add', $product), ['quantity' => 3])
        ->assertOk()
        ->assertJson(['count' => 3]);

    $cart = Cart::query()->where('user_id', $user->getKey())->firstOrFail();

    expect((int) $cart->items()->sum('quantity'))->toBe(3);
});

test('quantity requests are validated against live stock', function () {
    $product = Product::factory()->create(['stock_quantity' => 3]);

    $this->postJson(route('products.cart.add', $product), ['quantity' => 4])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity' => 'The quantity field must not be greater than 3.']);

    $this->postJson(route('products.cart.add', $product), ['quantity' => 0])
        ->assertStatus(422)
        ->assertJsonValidationErrors('quantity');

    $this->postJson(route('products.cart.add', $product), ['quantity' => 'two'])
        ->assertStatus(422)
        ->assertJsonValidationErrors('quantity');

    expect(session('guest_cart'))->toBeNull();
});

test('the per item cap is enforced even when stock is plentiful', function () {
    $product = Product::factory()->create(['stock_quantity' => 50]);

    $this->postJson(route('products.cart.add', $product), ['quantity' => 11])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity' => 'The quantity field must not be greater than 10.']);
});

test('out of stock products cannot be added through the json endpoint', function () {
    $product = Product::factory()->create(['stock_quantity' => 0]);

    $this->postJson(route('products.cart.add', $product), ['quantity' => 1])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['quantity' => 'This product is out of stock.']);
});

test('unpublished products cannot be added to the cart', function () {
    $product = Product::factory()->draft()->create();

    $this->postJson(route('products.cart.add', $product), ['quantity' => 1])->assertNotFound();

    expect(session('guest_cart'))->toBeNull();
});

test('client supplied prices and totals are ignored in favour of the database', function () {
    $product = Product::factory()->create([
        'price' => 49.00,
        'sale_price' => 39.00,
        'stock_quantity' => 5,
    ]);

    $this->post(route('products.cart.add', $product), [
        'quantity' => 1,
        'price' => '0.01',
        'sale_price' => '0.01',
        'unit_price' => '0.01',
        'total' => '0.01',
    ])->assertRedirect();

    $this->get(route('cart.index'))
        ->assertOk()
        ->assertSee('$39.00', false)
        ->assertDontSee('$0.01', false);
});
