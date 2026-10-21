<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Address;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\DemoDataSeeder;

test('the demo seeder creates a catalog, images, and a customer order', function () {
    $this->seed(DemoDataSeeder::class);

    expect(Category::query()->whereNull('parent_id')->count())->toBe(3)
        ->and(Category::query()->whereNotNull('parent_id')->count())->toBe(7)
        ->and(Product::query()->count())->toBe(10)
        ->and(Product::query()->doesntHave('images')->count())->toBe(0)
        ->and(Product::query()->where('status', '!=', 'active')->count())->toBe(0);

    $customer = User::query()->where('email', 'customer@example.com')->firstOrFail();
    $order = $customer->orders()->firstOrFail();

    expect($customer->addresses()->count())->toBe(1)
        ->and($order->status)->toBe(OrderStatus::Paid)
        ->and($order->items()->count())->toBe(2)
        ->and($order->payments()->count())->toBe(1)
        ->and($order->payments()->first()->status)->toBe(PaymentStatus::Succeeded);
});

test('the demo seeder prices match the product catalog', function () {
    $this->seed(DemoDataSeeder::class);

    $order = Order::query()->firstOrFail();
    $expectedTotal = (float) $order->items()->get()->sum(
        fn (OrderItem $item): float => ((float) $item->unit_price) * $item->quantity
    );

    expect((float) $order->total)->toBe($expectedTotal)
        ->and((float) $order->payments()->first()->amount)->toBe($expectedTotal);
});

test('re-running the demo seeder neither duplicates nor overwrites records', function () {
    $this->seed(DemoDataSeeder::class);

    $counts = fn (): array => [
        'categories' => Category::query()->count(),
        'products' => Product::query()->count(),
        'images' => ProductImage::query()->count(),
        'users' => User::query()->count(),
        'addresses' => Address::query()->count(),
        'orders' => Order::query()->count(),
        'order_items' => OrderItem::query()->count(),
        'payments' => Payment::query()->count(),
    ];

    $editedProduct = Product::query()->where('sku', 'SKU-PHONE-0001')->firstOrFail();
    $editedProduct->update(['name' => 'Renamed By Developer', 'price' => '123.45']);
    $seededCounts = $counts();

    $this->seed(DemoDataSeeder::class);

    expect($counts())->toBe($seededCounts)
        ->and($editedProduct->fresh()->name)->toBe('Renamed By Developer')
        ->and($editedProduct->fresh()->price)->toBe('123.45');
});

test('the demo seeder leaves pre-existing records untouched', function () {
    $existing = Product::factory()->create(['sku' => 'SKU-MINE-0001', 'name' => 'My Own Product']);

    $this->seed(DemoDataSeeder::class);

    $this->assertModelExists($existing);

    expect($existing->fresh()->name)->toBe('My Own Product');
});

test('the database seeder creates no records in production', function () {
    app()->detectEnvironment(fn (): string => 'production');

    (new DatabaseSeeder)->run();

    expect(User::query()->count())->toBe(0)
        ->and(Product::query()->count())->toBe(0);
});

test('the database seeder creates demo accounts and calls the demo data outside production', function () {
    (new DatabaseSeeder)->run();

    expect(User::query()->where('email', 'test@example.com')->exists())->toBeTrue()
        ->and(User::query()->where('email', 'admin@example.com')->first()->isAdmin())->toBeTrue()
        ->and(Product::query()->count())->toBeGreaterThan(0);
});
