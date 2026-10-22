<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use App\Support\Money;
use Illuminate\View\View;

class PolicyController extends Controller
{
    /**
     * The policy pages the storefront publishes.
     *
     * Each entry lists the slug used in the URL, the navigation label and
     * the heading shown on the page.
     *
     * @var array<string, string>
     */
    private const POLICIES = [
        'shipping' => 'Shipping',
        'returns' => 'Returns',
        'payments' => 'Payments',
        'privacy' => 'Privacy',
        'terms' => 'Terms',
    ];

    /**
     * Display a single policy page.
     */
    public function show(string $policy, SettingsService $settings): View
    {
        abort_unless(array_key_exists($policy, self::POLICIES), 404);

        return view('policies.show', [
            'policy' => $policy,
            'policyTitle' => self::POLICIES[$policy],
            'sections' => $this->sections($policy, $settings),
        ]);
    }

    /**
     * All published policies, for the storefront footer.
     *
     * @return array<string, string>
     */
    public static function available(): array
    {
        return self::POLICIES;
    }

    /**
     * Build the sections of a policy page from the store's real settings.
     *
     * Every figure quoted on these pages comes from the settings the store
     * actually uses at checkout, so the policies can never drift away from
     * the way the store behaves.
     *
     * @return list<array{heading: string, body: string}>
     */
    private function sections(string $policy, SettingsService $settings): array
    {
        $flatFee = Money::format($settings->shippingFlatFeeCents() / 100);
        $threshold = Money::format($settings->shippingFreeThresholdCents() / 100);
        $contactEmail = $settings->get('contact_email');

        return match ($policy) {
            'shipping' => [
                [
                    'heading' => 'Delivery charges',
                    'body' => "Standard delivery is charged at {$flatFee}. Orders with a subtotal of {$threshold} or more are delivered free of charge.",
                ],
                [
                    'heading' => 'Delivery times',
                    'body' => 'Orders are packed in the order they are received and usually arrive within '.$settings->get('delivery_estimate').'.',
                ],
                [
                    'heading' => 'Where we deliver',
                    'body' => 'We deliver to the address entered at checkout. If an address cannot be served, the order is paused and you are contacted before it ships.',
                ],
            ],
            'returns' => [
                [
                    'heading' => 'Return window',
                    'body' => 'Items may be returned within '.$settings->returnsWindowDays().' days of delivery.',
                ],
                [
                    'heading' => 'How to start a return',
                    'body' => 'Contact us with your order number and the item you wish to return. Returns are arranged from your account or by email, and items must be unused and in their original packaging.',
                ],
                [
                    'heading' => 'Refunds',
                    'body' => 'Approved refunds are returned to the original payment method. Delivery charges are only refundable when the return is caused by a fault with the item.',
                ],
            ],
            'payments' => [
                [
                    'heading' => 'Accepted methods',
                    'body' => $this->paymentSummary(),
                ],
                [
                    'heading' => 'Secure checkout',
                    'body' => 'Payment details are handled by the payment provider and are never stored on this store. Card data never touches the order record.',
                ],
                [
                    'heading' => 'Order confirmation',
                    'body' => 'A confirmation is shown on screen as soon as an order is placed, and the order stays listed in your account.',
                ],
            ],
            'privacy' => [
                [
                    'heading' => 'What we store',
                    'body' => 'We store the account details you provide when you register, the contact and delivery details attached to your orders, and the email address you submit to the store journal.',
                ],
                [
                    'heading' => 'How it is used',
                    'body' => 'Your details are used to process orders, to answer support enquiries and, where you have asked for it, to send the store journal. They are never sold on.',
                ],
                [
                    'heading' => 'Your choices',
                    'body' => 'You can update your account details and your delivery addresses at any time from your account, and you can unsubscribe from the journal from any message we send.',
                ],
            ],
            'terms' => [
                [
                    'heading' => 'Orders and pricing',
                    'body' => "Prices are shown in {$settings->get('store_currency')} and include the configured delivery charge, which is confirmed before an order is placed. Every price shown is the price that will be charged.",
                ],
                [
                    'heading' => 'Availability',
                    'body' => 'Stock levels are checked when an item is added to your cart. If an item sells out before checkout, the order is stopped and you are told before you are charged.',
                ],
                [
                    'heading' => 'Contacting us',
                    'body' => $contactEmail !== ''
                        ? "Questions about an order can be sent to {$contactEmail}."
                        : 'Questions about an order can be raised from your account, where your order history is listed.',
                ],
            ],
            default => [],
        };
    }

    /**
     * Describe the payment methods the store is actually configured for.
     */
    private function paymentSummary(): string
    {
        $driver = (string) config('payments.driver', 'cod');

        $methods = match ($driver) {
            'flutterwave' => 'Card payments are handled by Flutterwave, and cash on delivery is available where we deliver.',
            'cod' => 'Orders are placed on cash on delivery.',
            default => 'Orders are placed on cash on delivery.',
        };

        $currency = Money::currency();

        return $methods.' All amounts are settled in '.$currency.'.';
    }
}
