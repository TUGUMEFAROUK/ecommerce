<?php

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

test('the catalog lists active products and hides drafts and archived products', function () {
    Product::factory()->create(['name' => 'Visible Active Product']);
    Product::factory()->draft()->create(['name' => 'Hidden Draft Product']);
    Product::factory()->archived()->create(['name' => 'Hidden Archived Product']);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('Visible Active Product', false)
        ->assertDontSee('Hidden Draft Product', false)
        ->assertDontSee('Hidden Archived Product', false);
});

test('the catalog paginates product listings', function () {
    Product::factory()->create([
        'name' => 'Oldest Product',
        'created_at' => now()->subDay(),
    ]);
    Product::factory()->count(12)->create();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertDontSee('Oldest Product', false);

    $this->get(route('products.index', ['page' => 2]))
        ->assertOk()
        ->assertSee('Oldest Product', false);
});

test('the catalog shows an empty state when no products exist', function () {
    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('No products found', false)
        ->assertSee('Clear search and filters', false);
});

test('search narrows results to matching products', function () {
    Product::factory()->create(['name' => 'Alpha Camera']);
    Product::factory()->create(['name' => 'Beta Lamp']);

    $this->get(route('products.index', ['search' => 'Alpha']))
        ->assertOk()
        ->assertSee('Alpha Camera', false)
        ->assertDontSee('Beta Lamp', false);
});

test('search also matches the product sku', function () {
    Product::factory()->create(['name' => 'Alpha Camera', 'sku' => 'SKU-CAM-9999']);
    Product::factory()->create(['name' => 'Beta Lamp', 'sku' => 'SKU-LAMP-1111']);

    $this->get(route('products.index', ['search' => 'SKU-CAM']))
        ->assertOk()
        ->assertSee('Alpha Camera', false)
        ->assertDontSee('Beta Lamp', false);
});

test('a search without results shows the empty state', function () {
    Product::factory()->create(['name' => 'Alpha Camera']);

    $this->get(route('products.index', ['search' => 'no-such-product']))
        ->assertOk()
        ->assertSee('No products found', false)
        ->assertSee('Search results for', false);
});

test('the category filter shows only products from that category', function () {
    $phones = Category::factory()->create();
    $books = Category::factory()->create();

    Product::factory()->for($phones)->create(['name' => 'Phone Case']);
    Product::factory()->for($books)->create(['name' => 'Novel Title']);

    $this->get(route('products.index', ['category' => $phones->slug]))
        ->assertOk()
        ->assertSee('Phone Case', false)
        ->assertDontSee('Novel Title', false);
});

test('filtering by a parent category includes products from its child categories', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->childOf($parent)->create();

    Product::factory()->for($child)->create(['name' => 'Child Category Product']);

    $this->get(route('products.index', ['category' => $parent->slug]))
        ->assertOk()
        ->assertSee('Child Category Product', false);
});

test('products sort from low price to high price', function () {
    Product::factory()->create(['name' => 'Pricey Item', 'price' => 999.00]);
    Product::factory()->create(['name' => 'Cheap Item', 'price' => 9.99]);

    $this->get(route('products.index', ['sort' => 'price_asc']))
        ->assertOk()
        ->assertSeeInOrder(['Cheap Item', 'Pricey Item']);
});

test('products sort from high price to low price', function () {
    Product::factory()->create(['name' => 'Pricey Item', 'price' => 999.00]);
    Product::factory()->create(['name' => 'Cheap Item', 'price' => 9.99]);

    $this->get(route('products.index', ['sort' => 'price_desc']))
        ->assertOk()
        ->assertSeeInOrder(['Pricey Item', 'Cheap Item']);
});

test('newest products are the default sort order', function () {
    Product::factory()->create(['name' => 'Older Product', 'created_at' => now()->subDay()]);
    Product::factory()->create(['name' => 'Newer Product', 'created_at' => now()]);

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSeeInOrder(['Newer Product', 'Older Product']);
});

test('the availability filter can narrow to in-stock products', function () {
    Product::factory()->create(['name' => 'In Stock Item', 'stock_quantity' => 5]);
    Product::factory()->create(['name' => 'Out Stock Item', 'stock_quantity' => 0]);

    $this->get(route('products.index', ['availability' => 'in_stock']))
        ->assertOk()
        ->assertSee('In Stock Item', false)
        ->assertDontSee('Out Stock Item', false);
});

test('the availability filter can narrow to out-of-stock products', function () {
    Product::factory()->create(['name' => 'In Stock Item', 'stock_quantity' => 5]);
    Product::factory()->create(['name' => 'Out Stock Item', 'stock_quantity' => 0]);

    $this->get(route('products.index', ['availability' => 'out_of_stock']))
        ->assertOk()
        ->assertSee('Out Stock Item', false)
        ->assertDontSee('In Stock Item', false);
});

test('an invalid sort value is rejected', function () {
    $response = $this->from(route('products.index'))
        ->get(route('products.index', ['sort' => 'hax']));

    $response->assertRedirect(route('products.index'));
    $response->assertInvalid(['sort' => 'The selected sort is invalid.']);
});

test('an invalid availability value is rejected', function () {
    $response = $this->from(route('products.index'))
        ->get(route('products.index', ['availability' => 'maybe']));

    $response->assertRedirect(route('products.index'));
    $response->assertInvalid(['availability' => 'The selected availability is invalid.']);
});

test('an unknown category slug is rejected', function () {
    $response = $this->from(route('products.index'))
        ->get(route('products.index', ['category' => 'missing-category']));

    $response->assertRedirect(route('products.index'));
    $response->assertInvalid('category');
});

test('an oversized search term is rejected', function () {
    $response = $this->from(route('products.index'))
        ->get(route('products.index', ['search' => str_repeat('a', 101)]));

    $response->assertRedirect(route('products.index'));
    $response->assertInvalid('search');
});

test('filters are preserved in pagination links', function () {
    $category = Category::factory()->create();

    Product::factory()->count(13)->for($category)->create();

    $this->get(route('products.index', ['category' => $category->slug]))
        ->assertOk()
        ->assertSee('page=2', false)
        ->assertSee('category='.$category->slug, false);
});

test('the catalog page does not run per-product queries (no n+1)', function () {
    Product::factory()->count(12)->create();

    DB::enableQueryLog();
    $this->get(route('products.index'))->assertOk();
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect($queryCount)->toBeLessThan(12);
});

test('draft products are not listed by any catalog filter', function () {
    Product::factory()->draft()->create(['name' => 'Secret Draft']);

    $this->get(route('products.index', ['search' => 'Secret']))
        ->assertOk()
        ->assertDontSee('Secret Draft', false);
});

test('products with no matching status never leak through the availability filter', function () {
    Product::factory()->archived()->create(['name' => 'Archived Gear']);

    $this->get(route('products.index', ['availability' => 'in_stock']))
        ->assertOk()
        ->assertDontSee('Archived Gear', false);

    $this->get(route('products.index', ['availability' => 'out_of_stock']))
        ->assertOk()
        ->assertDontSee('Archived Gear', false);
});

test('the catalog header confirms the result count', function () {
    Product::factory()->count(2)->create();

    $this->get(route('products.index'))
        ->assertOk()
        ->assertSee('2 products', false);
});

test('catalog filter labels are associated with their inputs', function () {
    foreach (['search', 'category', 'sort', 'availability'] as $filter) {
        $this->get(route('products.index'))
            ->assertOk()
            ->assertSee('for="filter-'.$filter.'"', false);
    }
});
