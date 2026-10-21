<?php

namespace App\Payments;

use App\Models\Order;
use Illuminate\Support\Str;

/**
 * Hosted checkout via Flutterwave's Standard (Ramp) integration.
 *
 * Card data never touches this server: the customer is redirected to
 * Flutterwave's hosted payment page, and the order is only marked paid
 * after a webhook whose signature checks out AND a server-side verify
 * call confirms status, amount, currency, and tx_ref.
 */
class FlutterwaveGateway implements PaymentGateway
{
    public function __construct(private readonly FlutterwaveApiClient $api) {}

    public function name(): string
    {
        return 'flutterwave';
    }

    public function isConfigured(): bool
    {
        return filled(config('services.flutterwave.secret_key'))
            && filled(config('services.flutterwave.public_key'))
            && filled(config('services.flutterwave.secret_hash'));
    }

    public function supportsCurrency(string $currency): bool
    {
        return in_array(Str::upper($currency), config('payments.flutterwave.currencies', ['USD']), true);
    }

    public function createCheckout(Order $order): CheckoutSession
    {
        // Reuse the tx_ref already stored on the payment row so that a
        // retry (or an older hosted link the customer still has open)
        // verifies against the same reference.
        $reference = $order->payments()
            ->where('provider', $this->name())
            ->value('provider_reference');

        $reference ??= 'ORD-'.$order->getKey().'-'.Str::random(12);

        $data = $this->api->createPayment([
            'tx_ref' => $reference,
            'amount' => number_format((float) $order->total, 2, '.', ''),
            'currency' => (string) $order->currency,
            'redirect_url' => route('checkout.confirmation', $order),
            'customer' => [
                'email' => (string) ($order->contact_email ?: $order->user?->email),
                'name' => (string) ($order->contact_name ?: $order->user?->name),
            ],
            'meta' => [
                'order_id' => $order->getKey(),
            ],
        ]);

        $link = $data['link'] ?? null;

        if (! is_string($link) || $link === '') {
            throw new CheckoutException('The payment provider did not return a checkout link.');
        }

        return new CheckoutSession(
            reference: $reference,
            redirectUrl: $link,
        );
    }

    public function verifyTransaction(string $transactionId): ?array
    {
        return $this->api->verifyTransaction($transactionId);
    }
}
