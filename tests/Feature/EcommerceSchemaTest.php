<?php

use App\Enums\AddressType;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ProductStatus;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

test('the ecommerce tables exist', function () {
    $tables = [
        'categories',
        'products',
        'product_images',
        'carts',
        'cart_items',
        'orders',
        'order_items',
        'addresses',
        'payments',
    ];

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }
});

test('money columns are defined as decimal columns', function () {
    $moneyColumns = [
        'products.price',
        'orders.total',
        'order_items.unit_price',
        'order_items.line_total',
        'payments.amount',
    ];

    foreach ($moneyColumns as $qualifiedColumn) {
        [$table, $column] = explode('.', $qualifiedColumn);

        expect(Schema::getColumnType($table, $column))->toBeIn(['decimal', 'numeric']);
    }
});

test('product prices are returned with two decimal places', function () {
    $product = Product::factory()->create(['price' => '19.99']);

    expect($product->fresh()->price)->toBe('19.99');
});

test('products reject duplicate skus and slugs', function () {
    Product::factory()->create(['sku' => 'SKU-DUPLICATE', 'slug' => 'duplicate-product']);

    expect(fn () => Product::factory()->create(['sku' => 'SKU-DUPLICATE']))
        ->toThrow(QueryException::class);

    expect(fn () => Product::factory()->create(['slug' => 'duplicate-product']))
        ->toThrow(QueryException::class);
});

test('categories reject duplicate slugs', function () {
    Category::factory()->create(['slug' => 'shared-slug']);

    expect(fn () => Category::factory()->create(['slug' => 'shared-slug']))
        ->toThrow(QueryException::class);
});

test('payment provider references are unique', function () {
    Payment::factory()->create(['provider_reference' => 'PAY-UNIQUE-1']);

    expect(fn () => Payment::factory()->create(['provider_reference' => 'PAY-UNIQUE-1']))
        ->toThrow(QueryException::class);
});

test('the role attribute is protected from mass assignment', function () {
    $user = new User(['name' => 'Visitor', 'email' => 'visitor@example.com', 'role' => 'admin']);

    expect($user->role)->toBe(UserRole::Customer);
});

test('categories nest under parents and own products', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->childOf($parent)->create();
    $product = Product::factory()->for($child)->create();

    expect($child->parent->is($parent))->toBeTrue()
        ->and($parent->children->pluck('id')->all())->toBe([$child->id])
        ->and($child->products->pluck('id')->all())->toBe([$product->id]);
});

test('deleting a parent category keeps its children as root categories', function () {
    $parent = Category::factory()->create();
    $child = Category::factory()->childOf($parent)->create();

    $parent->delete();

    $this->assertDatabaseHas('categories', ['id' => $child->id, 'parent_id' => null]);
});

test('a category with products cannot be deleted', function () {
    $category = Category::factory()->has(Product::factory(), 'products')->create();

    expect(fn () => $category->delete())->toThrow(QueryException::class);
});

test('a product lists its images in position order', function () {
    $product = Product::factory()->create();
    $second = ProductImage::factory()->for($product)->create(['position' => 2]);
    $first = ProductImage::factory()->for($product)->create(['position' => 1]);

    expect($product->images->pluck('id')->all())->toBe([$first->id, $second->id]);
});

test('a user owns one cart, many orders, and many addresses', function () {
    $user = User::factory()->create();
    $cart = Cart::factory()->for($user)->create();
    $order = Order::factory()->for($user)->create();
    $address = Address::factory()->for($user)->create();

    expect($user->cart->is($cart))->toBeTrue()
        ->and($user->orders->pluck('id')->all())->toBe([$order->id])
        ->and($user->addresses->pluck('id')->all())->toBe([$address->id]);
});

test('a user cannot have two carts', function () {
    $user = User::factory()->create();
    Cart::factory()->for($user)->create();

    expect(fn () => Cart::factory()->for($user)->create())
        ->toThrow(QueryException::class);
});

test('a cart holds one row per product', function () {
    $cart = Cart::factory()->create();
    $product = Product::factory()->create();
    CartItem::factory()->for($cart)->for($product)->create(['quantity' => 2]);

    expect(fn () => CartItem::factory()->for($cart)->for($product)->create(['quantity' => 3]))
        ->toThrow(QueryException::class);
});

test('an order owns its items and payments', function () {
    $order = Order::factory()->create();
    $item = OrderItem::factory()->for($order)->create();
    $payment = Payment::factory()->for($order)->succeeded()->create();

    expect($order->items->pluck('id')->all())->toBe([$item->id])
        ->and($order->payments->pluck('id')->all())->toBe([$payment->id])
        ->and($payment->order->is($order))->toBeTrue();
});

test('order items keep the price paid after the product price changes', function () {
    $product = Product::factory()->create(['price' => '50.00']);
    $item = OrderItem::factory()->for($product)->create([
        'unit_price' => '50.00',
        'quantity' => 2,
        'line_total' => '100.00',
    ]);

    $product->update(['price' => '80.00']);
    $item = $item->fresh();

    expect($item->unit_price)->toBe('50.00')
        ->and($item->line_total)->toBe('100.00')
        ->and($item->product->price)->toBe('80.00');
});

test('status and type fields are cast to enums', function () {
    $product = Product::factory()->create();
    $order = Order::factory()->create();
    $payment = Payment::factory()->succeeded()->create();
    $address = Address::factory()->billing()->create();

    expect($product->status)->toBe(ProductStatus::Active)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->paid_at)->not->toBeNull()
        ->and($address->type)->toBe(AddressType::Billing);
});

test('deleting a product removes its images and cart rows but keeps order history', function () {
    $product = Product::factory()->has(ProductImage::factory()->count(2), 'images')->create();
    $cartItem = CartItem::factory()->for($product)->create();
    $orderItem = OrderItem::factory()->for($product)->create();

    $product->delete();

    $this->assertDatabaseMissing('product_images', ['product_id' => $product->id]);
    $this->assertDatabaseMissing('cart_items', ['id' => $cartItem->id]);
    $this->assertDatabaseHas('order_items', ['id' => $orderItem->id, 'product_id' => null]);
});

test('deleting a cart removes its items', function () {
    $cart = Cart::factory()->has(CartItem::factory()->count(2), 'items')->create();

    $cart->delete();

    $this->assertDatabaseMissing('cart_items', ['cart_id' => $cart->id]);
});

test('deleting an order removes its items but is blocked once a payment exists', function () {
    $order = Order::factory()->create();
    $item = OrderItem::factory()->for($order)->create();

    $order->delete();

    $this->assertDatabaseMissing('order_items', ['id' => $item->id]);

    $paidOrder = Order::factory()->create();
    Payment::factory()->for($paidOrder)->create();

    expect(fn () => $paidOrder->delete())->toThrow(QueryException::class);
});

test('a user with orders cannot be deleted', function () {
    $user = User::factory()->has(Order::factory(), 'orders')->create();

    expect(fn () => $user->delete())->toThrow(QueryException::class);
});

test('deleting a user removes their cart and addresses', function () {
    $user = User::factory()
        ->has(Cart::factory(), 'cart')
        ->has(Address::factory(), 'addresses')
        ->create();

    $user->delete();

    $this->assertDatabaseMissing('carts', ['user_id' => $user->id]);
    $this->assertDatabaseMissing('addresses', ['user_id' => $user->id]);
});
