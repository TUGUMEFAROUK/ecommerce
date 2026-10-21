<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentWebhookEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

beforeEach(function () {
    Http::preventStrayRequests();

    config(['services.flutterwave.secret_hash' => 'test-webhook-secret']);
});

/**
 * @return array{0: Order, 1: Payment}
 */
function webhookTargetOrder(array $paymentOverrides = []): array
{
    $order = Order::factory()->create([
        'status' => OrderStatus::Pending,
        'currency' => 'USD',
        'total' => 35.99,
    ]);

    $payment = Payment::create(array_merge([
        'order_id' => $order->getKey(),
        'provider' => 'flutterwave',
        'provider_reference' => 'ORD-'.$order->getKey().'-TESTREF',
        'amount' => 35.99,
        'currency' => 'USD',
        'status' => PaymentStatus::Pending,
    ], $paymentOverrides));

    return [$order, $payment];
}

/**
 * Post a webhook whose data defaults to match the given payment.
 */
function postFlutterwaveWebhook(Payment $payment, array $overrides = [], ?string $signature = 'test-webhook-secret'): TestResponse
{
    $body = array_replace_recursive([
        'event' => 'charge.completed',
        'data' => [
            'id' => 12345,
            'tx_ref' => $payment->provider_reference,
            'amount' => (float) $payment->amount,
            'currency' => (string) $payment->currency,
            'status' => 'successful',
        ],
    ], $overrides);

    $headers = $signature === null ? [] : ['verif-hash' => $signature];

    return test()->postJson(route('payments.flutterwave.webhook'), $body, $headers);
}

function flutterwaveVerifyResponse(Payment $payment, array $dataOverrides = [])
{
    return Http::response([
        'status' => 'success',
        'message' => 'Transaction fetched',
        'data' => array_merge([
            'id' => 12345,
            'tx_ref' => $payment->provider_reference,
            'amount' => (float) $payment->amount,
            'currency' => (string) $payment->currency,
            'status' => 'successful',
        ], $dataOverrides),
    ]);
}

test('webhooks without a matching signature are rejected', function () {
    [$order, $payment] = webhookTargetOrder();

    postFlutterwaveWebhook($payment, signature: null)->assertUnauthorized();
    postFlutterwaveWebhook($payment, signature: 'wrong')->assertUnauthorized();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and(PaymentWebhookEvent::query()->count())->toBe(0);
});

test('all webhooks are rejected while no secret hash is configured', function () {
    config(['services.flutterwave.secret_hash' => '']);

    [$order, $payment] = webhookTargetOrder();

    postFlutterwaveWebhook($payment, signature: '')->assertUnauthorized();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('a verified charge.completed marks the payment succeeded and the order paid', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment)]);

    postFlutterwaveWebhook($payment)->assertOk();

    $payment->refresh();
    $order->refresh();

    expect($payment->status)->toBe(PaymentStatus::Succeeded)
        ->and($payment->paid_at)->not->toBeNull()
        ->and($payment->provider_transaction_id)->toBe('12345')
        ->and($order->status)->toBe(OrderStatus::Paid);

    $history = $order->statusHistory()->latest('id')->firstOrFail();

    expect($history->from_status)->toBe(OrderStatus::Pending)
        ->and($history->to_status)->toBe(OrderStatus::Paid)
        ->and($history->user_id)->toBeNull()
        ->and($history->notes)->toContain('flutterwave');

    $event = PaymentWebhookEvent::query()->firstOrFail();

    expect($event->provider)->toBe('flutterwave')
        ->and($event->event_id)->toBe('charge.completed:12345')
        ->and($event->processed_at)->not->toBeNull();
});

test('a redelivered webhook is a no-op', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment)]);

    foreach (range(1, 3) as $attempt) {
        postFlutterwaveWebhook($payment)->assertOk();
    }

    expect(PaymentWebhookEvent::query()->count())->toBe(1)
        ->and($order->fresh()->status)->toBe(OrderStatus::Paid)
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($order->statusHistory()->count())->toBe(1);
});

test('the verify call must confirm the amount', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment, ['amount' => 100.00])]);

    postFlutterwaveWebhook($payment)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending)
        ->and($payment->fresh()->paid_at)->toBeNull()
        ->and(PaymentWebhookEvent::query()->firstOrFail()->processed_at)->not->toBeNull();
});

test('the verify call must confirm the currency', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment, ['currency' => 'NGN'])]);

    postFlutterwaveWebhook($payment)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('the verify call must confirm the tx_ref', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment, ['tx_ref' => 'ORD-1-SOMETHING-ELSE'])]);

    postFlutterwaveWebhook($payment)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('the verify call must report a successful transaction', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => flutterwaveVerifyResponse($payment, ['status' => 'pending'])]);

    postFlutterwaveWebhook($payment)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('an unreachable verify endpoint leaves the payment pending', function () {
    [$order, $payment] = webhookTargetOrder();

    Http::fake(['*/transactions/*/verify' => Http::response('', 500)]);

    postFlutterwaveWebhook($payment)->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('a webhook for an unknown payment is acknowledged and ignored', function () {
    $this->postJson(route('payments.flutterwave.webhook'), [
        'event' => 'charge.completed',
        'data' => ['id' => 999, 'tx_ref' => 'ORD-999-UNKNOWN', 'amount' => 35.99, 'currency' => 'USD'],
    ], ['verif-hash' => 'test-webhook-secret'])->assertOk();

    $this->assertDatabaseCount('payments', 0);
    $this->assertDatabaseCount('orders', 0);
});

test('a charge.failed marks a pending payment failed without touching the order', function () {
    [$order, $payment] = webhookTargetOrder();

    postFlutterwaveWebhook($payment, ['event' => 'charge.failed'])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Failed)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});

test('a charge.failed never downgrades a payment that already succeeded', function () {
    [$order, $payment] = webhookTargetOrder([
        'status' => PaymentStatus::Succeeded,
        'paid_at' => now(),
        'provider_transaction_id' => '12345',
    ]);

    $order->update(['status' => OrderStatus::Paid]);

    postFlutterwaveWebhook($payment, ['event' => 'charge.failed'])->assertOk();

    expect($payment->fresh()->status)->toBe(PaymentStatus::Succeeded)
        ->and($order->fresh()->status)->toBe(OrderStatus::Paid);
});

test('other event types are recorded but ignored', function () {
    [$order, $payment] = webhookTargetOrder();

    postFlutterwaveWebhook($payment, ['event' => 'subscription.charged'])->assertOk();

    expect(PaymentWebhookEvent::query()->firstOrFail()->processed_at)->not->toBeNull()
        ->and($payment->fresh()->status)->toBe(PaymentStatus::Pending)
        ->and($order->fresh()->status)->toBe(OrderStatus::Pending);
});
