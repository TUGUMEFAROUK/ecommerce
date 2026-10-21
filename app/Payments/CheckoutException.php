<?php

namespace App\Payments;

use RuntimeException;

/**
 * Thrown when a payment provider cannot start a checkout session.
 */
class CheckoutException extends RuntimeException {}
