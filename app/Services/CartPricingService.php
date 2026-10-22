<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartPricingService
{
    public function __construct(private readonly SettingsService $settings) {}

    /**
     * Price a set of cart lines using the current database prices.
     *
     * All arithmetic happens in integer cents so no floating point drift can
     * reach the database, and no value ever originates from the client.
     *
     * @param  Collection<int, array{product: Product, quantity: int}>  $lines
     * @return array{
     *     subtotal: string,
     *     shipping: string,
     *     discount: string,
     *     total: string,
     *     subtotal_cents: int,
     *     shipping_cents: int,
     *     discount_cents: int,
     *     total_cents: int
     * }
     */
    public function quote(Collection $lines): array
    {
        $subtotalCents = 0;

        foreach ($lines as $line) {
            $subtotalCents += $this->lineTotalCents($line['product']->effectivePrice(), $line['quantity']);
        }

        $flatShippingCents = $this->settings->shippingFlatFeeCents();
        $freeShippingThresholdCents = $this->settings->shippingFreeThresholdCents();

        $shippingCents = match (true) {
            $subtotalCents === 0, $subtotalCents >= $freeShippingThresholdCents => 0,
            default => $flatShippingCents,
        };

        // No discount rules are configured yet, so every quote carries a zero
        // discount. The grand total is always derived here, never accepted
        // from the browser.
        $discountCents = 0;
        $totalCents = $subtotalCents + $shippingCents - $discountCents;

        return [
            'subtotal' => $this->format($subtotalCents),
            'shipping' => $this->format($shippingCents),
            'discount' => $this->format($discountCents),
            'total' => $this->format($totalCents),
            'subtotal_cents' => $subtotalCents,
            'shipping_cents' => $shippingCents,
            'discount_cents' => $discountCents,
            'total_cents' => $totalCents,
        ];
    }

    /**
     * The total for a single line at the given unit price.
     */
    public function lineTotal(string|int|float $unitPrice, int $quantity): string
    {
        return $this->format($this->lineTotalCents($unitPrice, $quantity));
    }

    private function lineTotalCents(string|int|float $unitPrice, int $quantity): int
    {
        return (int) round(((float) $unitPrice) * 100) * $quantity;
    }

    private function format(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), abs($cents % 100));
    }
}
