<?php

namespace App\Payments;

use App\Models\Order;

interface PaymentGateway
{
    /**
     * The machine name stored on payment rows (e.g. "cod", "flutterwave").
     */
    public function name(): string;

    /**
     * Whether the gateway has everything it needs to take payments
     * (credentials, secret hash, ...). Unconfigured gateways must never
     * be offered at checkout.
     */
    public function isConfigured(): bool;

    /**
     * Whether the gateway can charge amounts in the given currency.
     */
    public function supportsCurrency(string $currency): bool;

    /**
     * Start a hosted checkout session for the order.
     *
     * @throws CheckoutException when the provider refuses the request or
     *                           does not return a usable checkout link
     */
    public function createCheckout(Order $order): CheckoutSession;

    /**
     * Ask the provider to confirm a transaction server-side.
     *
     * Returns the verified transaction payload (status, amount, currency,
     * tx_ref, ...) or null when the provider could not confirm it. Callers
     * must never treat null as success.
     */
    public function verifyTransaction(string $transactionId): ?array;
}
