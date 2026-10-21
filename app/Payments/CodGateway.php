<?php

namespace App\Payments;

use App\Models\Order;

/**
 * Cash on delivery: no hosted checkout, no server-side verification.
 * The payment stays pending until it is actually collected.
 */
class CodGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'cod';
    }

    public function isConfigured(): bool
    {
        return true;
    }

    public function supportsCurrency(string $currency): bool
    {
        return true;
    }

    public function createCheckout(Order $order): CheckoutSession
    {
        throw new CheckoutException('Cash on delivery does not use a hosted checkout.');
    }

    public function verifyTransaction(string $transactionId): ?array
    {
        return null;
    }
}
