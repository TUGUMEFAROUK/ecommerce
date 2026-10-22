<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function adminProductPayload(Category $category, array $overrides = []): array
{
    return array_merge([
        'category_id' => $category->getKey(),
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'description' => 'A comfortable wireless mouse.',
        'price' => '29.99',
        'sku' => 'SKU-MOUSE-1',
        'stock_quantity' => 5,
        'status' => 'active',
    ], $overrides);
}

test('the product index lists products and filters by search, status, and category', function () {
    $category = Category::factory()->create(['name' => 'Peripherals', 'slug' => 'peripherals']);
    $other = Category::factory()->create();
    $mouse = Product::factory()->create(['name' => 'Wireless Mouse', 'sku' => 'SKU-MOUSE-1', 'category_id' => $category->getKey()]);
    Product::factory()->draft()->create(['name' => 'Keyboard', 'category_id' => $other->getKey()]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Wireless Mouse', false)
        ->assertSee('Keyboard', false);

    $this->actingAs($admin)->get(route('admin.products.index', ['search' => 'MOUSE']))
        ->assertOk()
        ->assertSee('Wireless Mouse', false)
        ->assertDontSee('Keyboard', false);

    $this->actingAs($admin)->get(route('admin.products.index', ['status' => 'draft']))
        ->assertOk()
        ->assertSee('Keyboard', false)
        ->assertDontSee('Wireless Mouse', false);

    $this->actingAs($admin)->get(route('admin.products.index', ['category' => $other->getKey()]))
        ->assertOk()
        ->assertSee('Keyboard', false)
        ->assertDontSee('Wireless Mouse', false);
});

test('an administrator can create a product', function () {
    $category = Category::factory()->create();

    $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.store'), adminProductPayload($category));

    $response->assertRedirect();

    $this->assertDatabaseHas('products', [
        'name' => 'Wireless Mouse',
        'slug' => 'wireless-mouse',
        'sku' => 'SKU-MOUSE-1',
        'category_id' => $category->getKey(),
        'stock_quantity' => 5,
        'status' => 'active',
    ]);
});

test('product creation validates required fields and formats', function () {
    $category = Category::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), [])
        ->assertInvalid([
            'name',
            'slug',
            'price',
            'sku',
            'stock_quantity',
            'status',
            'category_id',
        ]);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), adminProductPayload($category, ['slug' => 'Not A Slug']))
        ->assertInvalid(['slug' => 'The slug field format is invalid.']);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), adminProductPayload($category, ['price' => '-1.00']))
        ->assertInvalid(['price' => 'The price field must be at least 0.']);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), adminProductPayload($category, ['status' => 'discontinued']))
        ->assertInvalid(['status' => 'The selected status is invalid.']);

    $this->assertDatabaseCount('products', 0);
});

test('duplicate slugs and skus are rejected', function () {
    $category = Category::factory()->create();
    Product::factory()->create([
        'slug' => 'taken-slug',
        'sku' => 'SKU-TAKEN',
        'category_id' => $category->getKey(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), adminProductPayload($category, ['slug' => 'taken-slug', 'sku' => 'SKU-OTHER']))
        ->assertInvalid(['slug' => 'The slug has already been taken.']);

    $this->actingAs($admin)
        ->post(route('admin.products.store'), adminProductPayload($category, ['slug' => 'other-slug', 'sku' => 'SKU-TAKEN']))
        ->assertInvalid(['sku' => 'The sku has already been taken.']);
});

test('updating a product ignores its own slug and sku when checking uniqueness', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'slug' => 'existing-slug',
        'sku' => 'SKU-EXISTING',
        'category_id' => $category->getKey(),
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.products.update', $product), adminProductPayload($category, [
            'name' => 'Renamed Mouse',
            'slug' => 'existing-slug',
            'sku' => 'SKU-EXISTING',
        ]))
        ->assertRedirect();

    expect($product->refresh()->name)->toBe('Renamed Mouse');

    $this->assertDatabaseHas('products', ['slug' => 'existing-slug']);
});

test('updating a product rejects another products slug or sku', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['slug' => 'other-slug', 'sku' => 'SKU-OTHER', 'category_id' => $category->getKey()]);
    $product = Product::factory()->create(['category_id' => $category->getKey()]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), adminProductPayload($category, ['slug' => 'other-slug']))
        ->assertInvalid(['slug' => 'The slug has already been taken.']);

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), adminProductPayload($category, ['sku' => 'SKU-OTHER']))
        ->assertInvalid(['sku' => 'The sku has already been taken.']);
});

test('changing the stock quantity from the product form records a movement', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'stock_quantity' => 4,
        'category_id' => $category->getKey(),
    ]);

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin)
        ->put(route('admin.products.update', $product), adminProductPayload($category, ['stock_quantity' => 12]))
        ->assertRedirect();

    $this->assertDatabaseHas('stock_movements', [
        'product_id' => $product->getKey(),
        'user_id' => $admin->getKey(),
        'change' => 8,
        'stock_after' => 12,
        'reason' => 'Product form',
    ]);

    expect($product->refresh()->stock_quantity)->toBe(12);
});

test('updating a product without changing the stock records no movement', function () {
    $category = Category::factory()->create();
    $product = Product::factory()->create([
        'stock_quantity' => 7,
        'category_id' => $category->getKey(),
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.products.update', $product), adminProductPayload($category, ['stock_quantity' => 7]))
        ->assertRedirect();

    $this->assertDatabaseCount('stock_movements', 0);
});

test('deleting a product removes its records and image files', function () {
    Storage::fake('public');

    $product = Product::factory()->create();
    $path = UploadedFile::fake()->createWithContent('mouse.png', base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==',
    ))->store('products', 'public');

    $product->images()->create(['path' => $path, 'position' => 0, 'is_primary' => true]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->delete(route('admin.products.destroy', $product))
        ->assertRedirect(route('admin.products.index'));

    $this->assertDatabaseMissing('products', ['id' => $product->getKey()]);
    $this->assertDatabaseCount('product_images', 0);
    Storage::disk('public')->assertMissing($path);
});

test('the product form rejects an uploaded svg', function () {
    Storage::fake('public');

    $product = Product::factory()->create();

    $svg = UploadedFile::fake()->createWithContent(
        'evil.svg',
        '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
    );

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.images.store', $product), ['image' => $svg])
        ->assertInvalid(['image']);

    $this->assertDatabaseCount('product_images', 0);
});

test('an administrator can set a sale price below the list price', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.store'), adminProductPayload($category, [
            'sale_price' => '19.99',
        ]))
        ->assertRedirect();

    expect(Product::query()->sole()->sale_price)->toBe('19.99');
});

test('a sale price that is not below the list price is rejected', function () {
    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->post(route('admin.products.store'), adminProductPayload($category, [
            'sale_price' => '29.99',
        ]))
        ->assertInvalid(['sale_price']);

    $this->assertDatabaseCount('products', 0);
});

test('an empty sale price on update clears the discount', function () {
    $category = Category::factory()->create();

    $product = Product::factory()->create([
        'category_id' => $category->getKey(),
        'price' => 29.99,
        'sale_price' => 19.99,
    ]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('admin.products.update', $product), adminProductPayload($category, [
            'sale_price' => '',
        ]))
        ->assertRedirect();

    expect($product->refresh()->sale_price)->toBeNull();
});

test('the product form renders the sale price field', function () {
    Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('admin.products.create'))
        ->assertOk()
        ->assertSee('name="sale_price"', false)
        ->assertSee('Sale price', false);
});

test('customers are denied access to every product management route', function (string $method, string $routeName) {
    $category = Category::factory()->create();
    $product = Product::factory()->create(['category_id' => $category->getKey()]);
    $customer = User::factory()->create(['role' => 'customer']);

    $payload = $method === 'get' ? [] : adminProductPayload($category);
    $parameters = in_array($routeName, ['admin.products.index', 'admin.products.create', 'admin.products.store'], true)
        ? []
        : [$product];

    $this->actingAs($customer)
        ->call($method, route($routeName, $parameters), $payload)
        ->assertForbidden();
})->with([
    ['get', 'admin.products.index'],
    ['get', 'admin.products.create'],
    ['post', 'admin.products.store'],
    ['get', 'admin.products.show'],
    ['get', 'admin.products.edit'],
    ['put', 'admin.products.update'],
    ['delete', 'admin.products.destroy'],
]);

test('guests are redirected to login from every product management route', function () {
    $product = Product::factory()->create();

    $this->get(route('admin.products.index'))->assertRedirect(route('login'));
    $this->get(route('admin.products.show', $product))->assertRedirect(route('login'));
    $this->delete(route('admin.products.destroy', $product))->assertRedirect(route('login'));
    $this->assertDatabaseCount('products', 1);
});
