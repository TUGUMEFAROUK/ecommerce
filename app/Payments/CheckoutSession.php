<?php

namespace App\Payments;

/**
 * A hosted checkout session the customer must be redirected to.
 */
readonly class CheckoutSession
{
    public function __construct(
        /**
         * The merchant-side transaction reference (tx_ref) this session
         * was created with; persisted on the payment row.
         */
        public string $reference,

        /** The hosted payment page URL to redirect the customer to. */
        public string $redirectUrl,
    ) {}
}
