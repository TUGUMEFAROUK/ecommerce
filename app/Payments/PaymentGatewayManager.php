<?php

namespace App\Payments;

use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * Resolve a payment gateway by name; defaults to the configured driver.
     */
    public function driver(?string $name = null): PaymentGateway
    {
        $name ??= (string) config('payments.driver', 'cod');

        return match ($name) {
            'cod' => app(CodGateway::class),
            'flutterwave' => app(FlutterwaveGateway::class),
            default => throw new InvalidArgumentException(sprintf('Unsupported payment driver [%s].', $name)),
        };
    }
}
