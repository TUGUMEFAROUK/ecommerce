<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();
});

function flutterwaveCheckoutConfig(): void
{
    config([
        'services.flutterwave.public_key' => 'FLWPUBK-test-key',
        'services.flutterwave.secret_key' => 'FLWSECK-test-key',
        'services.flutterwave.secret_hash' => 'test-webhook-secret',
    ]);
}

function flutterwaveCheckoutPayload(array $overrides = []): array
{
    return array_merge([
        'contact_name' => 'Jane Doe',
        'contact_email' => 'jane@example.com',
        'contact_phone' => '555-0100',
        'shipping_line1' => '123 Main Street',
        'shipping_line2' => 'Apartment 4',
        'shipping_city' => 'Springfield',
        'shipping_state' => 'Illinois',
        'shipping_postal_code' => '62704',
        'shipping_country' => 'US',
        'payment_method' => 'flutterwave',
    ], $overrides);
}

function flutterwaveHostedLinkResponse()
{
    return [
        'status' => 'success',
        'message' => 'Link created',
        'data' => [
            'link' => 'https://checkout.flutterwave.com/v3/hosted/pay/test-session',
        ],
    ];
}

test('the checkout page only offers flutterwave when it is configured', function () {
    $user = User::factory()->create();
    Product::factory()->create(['price' => 10.00, 'stock_quantity' => 5]);

    $this->actingAs($user)->post(route('products.cart.add', Product::query()->first()), ['quantity' => 1]);

    $this->actingAs($user)
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('Online card payments are not yet available', false)
        ->assertDontSee('value="flutterwave"', false);

    flutterwaveCheckoutConfig();

    $this->actingAs($user)
        ->get(route('checkout.show'))
        ->assertOk()
        ->assertSee('value="flutterwave"', false);
});

test('posting flutterwave while it is unconfigured is rejected and creates no order', function () {
    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 5]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), flutterwaveCheckoutPayload())
        ->assertInvalid(['payment_method' => 'Online card payments are not configured for this store yet.']);

    $this->assertDatabaseCount('orders', 0);
});

test('flutterwave is rejected when it does not support the store currency', function () {
    flutterwaveCheckoutConfig();

    Setting::query()->create(['key' => 'store_currency', 'value' => 'GBP']);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 10.00, 'stock_quantity' => 5]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), flutterwaveCheckoutPayload())
        ->assertInvalid(['payment_method' => 'Online card payments are not available in GBP.']);

    $this->assertDatabaseCount('orders', 0);
});

test('a flutterwave checkout creates a pending payment and redirects to the hosted page', function () {
    flutterwaveCheckoutConfig();

    Http::fake(['*/v3/payments' => Http::response(flutterwaveHostedLinkResponse())]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 12.50, 'stock_quantity' => 10, 'sku' => 'SKU-FW-1']);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 2]);

    $this->actingAs($user)
        ->post(route('checkout.store'), flutterwaveCheckoutPayload())
        ->assertRedirect('https://checkout.flutterwave.com/v3/hosted/pay/test-session');

    $order = Order::query()->firstOrFail();
    $payment = Payment::query()->where('order_id', $order->getKey())->firstOrFail();

    expect($payment->provider)->toBe('flutterwave')
        ->and($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->paid_at)->toBeNull()
        ->and($payment->provider_reference)->toStartWith('ORD-'.$order->getKey().'-');

    $request = Http::recorded()->last()[0];

    expect(json_decode($request->body(), true))
        ->toMatchArray([
            'tx_ref' => $payment->provider_reference,
            'amount' => '30.99',
            'currency' => 'USD',
        ]);
});

test('if the provider refuses the checkout the order is kept and a retryable error is shown', function () {
    flutterwaveCheckoutConfig();

    Http::fake(['*/v3/payments' => Http::response(['status' => 'error', 'message' => 'Temporarily unavailable'], 400)]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 12.50, 'stock_quantity' => 10]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), flutterwaveCheckoutPayload())
        ->assertInvalid(['payment_method' => 'The payment provider could not start the checkout. Your order has been saved — use "Complete payment" on the confirmation page to try again.']);

    $payment = Payment::query()->firstOrFail();

    expect($payment->status)->toBe(PaymentStatus::Pending)
        ->and($payment->provider_reference)->toBeNull();

    $order = Order::query()->firstOrFail();

    $this->actingAs($user)
        ->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertSee('Complete payment', false);
});

test('retrying a pending flutterwave payment reuses the same tx_ref', function () {
    flutterwaveCheckoutConfig();

    Http::fake(['*/v3/payments' => Http::response(flutterwaveHostedLinkResponse())]);

    $user = User::factory()->create();
    $product = Product::factory()->create(['price' => 20.00, 'stock_quantity' => 10]);

    $this->actingAs($user)->post(route('products.cart.add', $product), ['quantity' => 1]);

    $this->actingAs($user)
        ->post(route('checkout.store'), flutterwaveCheckoutPayload())
        ->assertRedirect('https://checkout.flutterwave.com/v3/hosted/pay/test-session');

    $order = Order::query()->firstOrFail();
    $originalReference = Payment::query()->where('order_id', $order->getKey())->firstOrFail()->provider_reference;

    $this->actingAs($user)
        ->post(route('checkout.pay', $order))
        ->assertRedirect('https://checkout.flutterwave.com/v3/hosted/pay/test-session');

    $txRefs = Http::recorded()
        ->map(fn ($pair): ?string => json_decode($pair[0]->body(), true)['tx_ref'] ?? null)
        ->filter()
        ->all();

    expect($txRefs)->each->toBe($originalReference);
    expect(Payment::query()->where('order_id', $order->getKey())->count())->toBe(1);
});

test('paying again is refused when the payment is no longer pending', function () {
    flutterwaveCheckoutConfig();

    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->getKey(), 'status' => OrderStatus::Pending]);

    Payment::create([
        'order_id' => $order->getKey(),
        'provider' => 'flutterwave',
        'provider_reference' => 'ORD-'.$order->getKey().'-REF',
        'amount' => $order->total,
        'currency' => $order->currency,
        'status' => PaymentStatus::Succeeded,
        'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->post(route('checkout.pay', $order))
        ->assertRedirect(route('checkout.confirmation', $order))
        ->assertSessionHas('status', 'This order has no pending payment.');
});

test('customers cannot reopen the checkout for someone elses order', function () {
    flutterwaveCheckoutConfig();

    $owner = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $owner->getKey(), 'status' => OrderStatus::Pending]);

    Payment::create([
        'order_id' => $order->getKey(),
        'provider' => 'flutterwave',
        'provider_reference' => 'ORD-'.$order->getKey().'-REF',
        'amount' => $order->total,
        'currency' => $order->currency,
        'status' => PaymentStatus::Pending,
    ]);

    $this->actingAs(User::factory()->create())
        ->post(route('checkout.pay', $order))
        ->assertNotFound();
});

test('the confirmation page shows the paid state for a succeeded flutterwave payment', function () {
    $user = User::factory()->create();
    $order = Order::factory()->create(['user_id' => $user->getKey(), 'status' => OrderStatus::Paid, 'total' => 35.99]);

    Payment::create([
        'order_id' => $order->getKey(),
        'provider' => 'flutterwave',
        'provider_reference' => 'ORD-'.$order->getKey().'-REF',
        'provider_transaction_id' => '12345',
        'amount' => 35.99,
        'currency' => 'USD',
        'status' => PaymentStatus::Succeeded,
        'paid_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('checkout.confirmation', $order))
        ->assertOk()
        ->assertSee('Paid USD 35.99', false)
        ->assertDontSee('Complete payment', false);
});
