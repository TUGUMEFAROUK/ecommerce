<?php

use App\Enums\UserRole;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('the admin shell renders the navigation, top bar and skip link', function () {
    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('Skip to content')
        ->assertSee('View store')
        ->assertSee('Admin panel')
        ->assertSee('aria-current="page"', false);
});

test('the product list shows thumbnails, status badges and a confirmation control', function () {
    Storage::fake('public');

    $category = Category::factory()->create(['name' => 'Textiles']);
    $product = Product::factory()->for($category)->create([
        'name' => 'Handwoven Throw',
        'sku' => 'THROW-01',
        'stock_quantity' => 0,
    ]);

    $product->images()->create([
        'path' => 'products/throw.png',
        'position' => 0,
        'is_primary' => true,
    ]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('Handwoven Throw')
        ->assertSee('THROW-01')
        ->assertSee('Textiles')
        ->assertSee('Out of stock')
        ->assertSee('data-confirm="Delete Handwoven Throw? This removes the product and its stored images."', false);
});

test('the product list can be filtered by status and search term', function () {
    Product::factory()->create(['name' => 'Active Lamp', 'status' => 'active']);
    Product::factory()->create(['name' => 'Draft Lamp', 'status' => 'draft']);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.products.index', ['status' => 'draft']))
        ->assertOk()
        ->assertSee('Draft Lamp')
        ->assertDontSee('Active Lamp');

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.products.index', ['search' => 'Active']))
        ->assertOk()
        ->assertSee('Active Lamp')
        ->assertDontSee('Draft Lamp');
});

test('the product list paginates with the admin pagination view', function () {
    Product::factory()->count(12)->create();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.products.index'))
        ->assertOk()
        ->assertSee('of')
        ->assertSee('12')
        ->assertSee('Next');
});

test('a product can be created with a primary image from the form', function () {
    Storage::fake('public');

    $category = Category::factory()->create();

    $response = $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Ceramic Mug',
            'slug' => 'ceramic-mug',
            'description' => 'A sturdy handmade mug.',
            'price' => 24.00,
            'sale_price' => '',
            'sku' => 'MUG-01',
            'stock_quantity' => 12,
            'status' => 'active',
            'featured' => '1',
            'image_upload' => UploadedFile::fake()->createWithContent(
                'mug.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
            ),
        ]);

    $product = Product::query()->where('slug', 'ceramic-mug')->firstOrFail();

    $response->assertRedirect(route('admin.products.show', $product));

    expect($product->images)->toHaveCount(1)
        ->and($product->images->first()->is_primary)->toBeTrue();

    Storage::disk('public')->assertExists($product->images->first()->path);
});

test('the product form rejects an upload that is not an image', function () {
    Storage::fake('public');

    $category = Category::factory()->create();

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->post(route('admin.products.store'), [
            'category_id' => $category->id,
            'name' => 'Ceramic Mug',
            'slug' => 'ceramic-mug',
            'price' => 24.00,
            'sku' => 'MUG-01',
            'stock_quantity' => 12,
            'status' => 'active',
            'image_upload' => UploadedFile::fake()->createWithContent('notes.txt', 'not an image'),
        ])
        ->assertSessionHasErrors('image_upload');

    expect(Product::query()->where('slug', 'ceramic-mug')->exists())->toBeFalse();
});

test('the product edit form previews the current images', function () {
    Storage::fake('public');

    $product = Product::factory()->create(['name' => 'Linen Napkin']);

    $product->images()->create([
        'path' => 'products/napkin.png',
        'position' => 0,
        'is_primary' => true,
    ]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.products.edit', $product))
        ->assertOk()
        ->assertSee('Linen Napkin')
        ->assertSee('Primary')
        ->assertSee('Replace the primary image');
});

test('the order screen shows the items, the payment state and the status timeline', function () {
    $order = Order::factory()->paid()->create(['total' => 120.00]);

    $order->items()->create([
        'product_id' => null,
        'product_name' => 'Handwoven Throw',
        'product_sku' => 'THROW-01',
        'unit_price' => 60.00,
        'quantity' => 2,
        'line_total' => 120.00,
    ]);

    $order->payments()->create([
        'provider' => 'stripe',
        'status' => 'succeeded',
        'currency' => 'USD',
        'amount' => 120.00,
        'paid_at' => now(),
    ]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.orders.show', $order))
        ->assertOk()
        ->assertSee('Handwoven Throw')
        ->assertSee('THROW-01')
        ->assertSee('Succeeded')
        ->assertSee('Paid');
});

test('the customer screen shows the order count and the recognized spend', function () {
    $customer = User::factory()->create(['name' => 'Grace Hopper']);

    Order::factory()->for($customer)->paid()->create(['total' => 80.00]);
    Order::factory()->for($customer)->create(['total' => 500.00]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.customers.show', $customer))
        ->assertOk()
        ->assertSee('Grace Hopper')
        ->assertSee('USD 80.00', false)
        ->assertSee('Recognized sales only');
});

test('the inventory screen shows the threshold notice and the movement history', function () {
    Product::factory()->create(['name' => 'Low Stock Vase', 'stock_quantity' => 1]);

    $this->actingAs(User::factory()->create(['role' => UserRole::Admin]))
        ->get(route('admin.inventory.index'))
        ->assertOk()
        ->assertSee('Low Stock Vase')
        ->assertSee('audit trail')
        ->assertSee('Save');
});

test('every admin screen renders for an administrator', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);

    $category = Category::factory()->create();
    $product = Product::factory()->for($category)->create();
    $customer = User::factory()->create();
    $order = Order::factory()->for($customer)->create();

    $routes = [
        route('admin.dashboard'),
        route('admin.categories.index'),
        route('admin.categories.create'),
        route('admin.categories.edit', $category),
        route('admin.customers.index'),
        route('admin.customers.show', $customer),
        route('admin.inventory.index'),
        route('admin.orders.index'),
        route('admin.orders.show', $order),
        route('admin.products.index'),
        route('admin.products.create'),
        route('admin.products.show', $product),
        route('admin.products.edit', $product),
        route('admin.settings.edit'),
    ];

    foreach ($routes as $url) {
        $this->actingAs($admin)
            ->get($url)
            ->assertOk();
    }
});

test('the storefront links to the admin panel for administrators only', function () {
    $admin = User::factory()->create(['role' => UserRole::Admin]);
    $customer = User::factory()->create(['role' => UserRole::Customer]);

    $this->actingAs($admin)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('admin.dashboard'), false);

    $this->actingAs($admin)
        ->get(route('account.show'))
        ->assertOk()
        ->assertSee(route('admin.dashboard'), false)
        ->assertSee('Admin panel');

    $this->actingAs($customer)
        ->get(route('home'))
        ->assertOk()
        ->assertDontSee(route('admin.dashboard'), false);

    $this->actingAs($customer)
        ->get(route('account.show'))
        ->assertOk()
        ->assertDontSee(route('admin.dashboard'), false);
});
