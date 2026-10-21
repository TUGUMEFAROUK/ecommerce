<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the homepage renders with a hero and storefront navigation', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Everything you need', false)
        ->assertSee('Browse all products', false)
        ->assertSee('Featured products', false)
        ->assertSee('New arrivals', false);
});

test('the homepage surfaces featured products even when they are not the newest', function () {
    Product::factory()->featured()->create([
        'name' => 'Featured Gadget',
        'created_at' => now()->subDays(3),
    ]);

    Product::factory()->count(8)->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Featured Gadget', false);
});

test('the homepage does not list non-featured products in the featured section', function () {
    Product::factory()->featured()->create([
        'name' => 'Featured Gadget',
        'created_at' => now()->subDays(3),
    ]);

    Product::factory()->create([
        'name' => 'Regular Widget',
        'created_at' => now()->subDays(2),
    ]);

    $response = $this->get(route('home'));

    $featuredSection = substr(
        $response->getContent(),
        strpos($response->getContent(), 'id="featured-heading"'),
        strpos($response->getContent(), 'id="new-arrivals-heading"') - strpos($response->getContent(), 'id="featured-heading"'),
    );

    expect($featuredSection)
        ->toContain('Featured Gadget')
        ->not->toContain('Regular Widget');
});

test('the homepage shows empty states when the catalog is empty', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('No featured products yet', false)
        ->assertSee('No products in the catalog', false);
});

test('the storefront header provides search, category navigation, and a footer', function () {
    $category = Category::factory()->create(['name' => 'Photo Gear']);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('role="search"', false)
        ->assertSee('name="search"', false)
        ->assertSee(route('products.index', ['category' => $category->slug]), false)
        ->assertSee('checkout and payment processing are not yet available', false);
});

test('storefront pages use a responsive viewport and responsive product grids', function () {
    Product::factory()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('name="viewport"', false)
        ->assertSee('sm:grid-cols', false)
        ->assertSee('lg:grid-cols', false);
});

test('storefront pages include accessible landmarks and a skip link', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('id="main-content"', false)
        ->assertSee('Skip to content', false)
        ->assertSee('<main', false)
        ->assertSee('<footer', false)
        ->assertSee('aria-label="Categories"', false);
});

test('the homepage headline is escaped as plain text', function () {
    config(['app.name' => 'Shop <script>alert(1)</script>']);

    $response = $this->get(route('home'));

    $response->assertOk();
    expect($response->getContent())
        ->toContain('Shop &lt;script&gt;')
        ->not->toContain('Shop <script>');
});
