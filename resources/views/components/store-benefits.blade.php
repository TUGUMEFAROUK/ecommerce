@php
    use App\Services\SettingsService;
    use App\Support\Money;

    $settings = app(SettingsService::class);
    $flatFee = Money::format($settings->shippingFlatFeeCents() / 100);
    $threshold = Money::format($settings->shippingFreeThresholdCents() / 100);
    $paymentDriver = (string) config('payments.driver', 'cod');
    $paymentMethod = $paymentDriver === 'flutterwave'
        ? 'Card payments processed by Flutterwave'
        : 'Cash on delivery, or card payment at checkout';
    $contactEmail = $settings->get('contact_email');
@endphp

<ul class="grid gap-px overflow-hidden rounded-sm border border-sand bg-sand sm:grid-cols-2 lg:grid-cols-4">
    <li class="bg-white p-7">
        <svg class="h-6 w-6 text-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
            <path d="M3.5 7.5h11v9h-11zM14.5 10.5h3l3 3v3h-6z" stroke-linejoin="round" />
            <circle cx="7" cy="18" r="1.5" /><circle cx="17.5" cy="18" r="1.5" />
        </svg>
        <h3 class="mt-4 font-display text-xl">Delivery</h3>
        <p class="mt-2 text-sm leading-relaxed text-charcoal/70">
            {{ $flatFee }} delivery, free on orders over {{ $threshold }}.
        </p>
    </li>

    <li class="bg-white p-7">
        <svg class="h-6 w-6 text-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
            <rect x="4.5" y="10" width="15" height="9.5" rx="1.5" />
            <path d="M8 10V7.5a4 4 0 0 1 8 0V10" stroke-linecap="round" />
        </svg>
        <h3 class="mt-4 font-display text-xl">Secure payments</h3>
        <p class="mt-2 text-sm leading-relaxed text-charcoal/70">
            {{ $paymentMethod }}.
        </p>
    </li>

    <li class="bg-white p-7">
        <svg class="h-6 w-6 text-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
            <path d="M4.5 8.5h11a3 3 0 0 1 3 3v5a3 3 0 0 1-3 3h-11z" stroke-linejoin="round" />
            <path d="M8 8.5V6a2 2 0 0 1 2-2h5" stroke-linecap="round" />
            <path d="M11 12.5h6.5" stroke-linecap="round" />
        </svg>
        <h3 class="mt-4 font-display text-xl">Returns</h3>
        <p class="mt-2 text-sm leading-relaxed text-charcoal/70">
            {{ $settings->returnsWindowDays() }} days to change your mind on any order.
        </p>
    </li>

    <li class="bg-white p-7">
        <svg class="h-6 w-6 text-olive" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" aria-hidden="true">
            <path d="M4 12a8 8 0 0 1 13.7-5.6L20 8.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20 4.5v4h-4" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M20 12a8 8 0 0 1-13.7 5.6L4 15.5" stroke-linecap="round" stroke-linejoin="round" />
            <path d="M4 19.5v-4h4" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <h3 class="mt-4 font-display text-xl">Support</h3>
        <p class="mt-2 text-sm leading-relaxed text-charcoal/70">
            @if ($contactEmail !== '')
                <a href="mailto:{{ $contactEmail }}" class="link-quiet">Email us</a> and we will reply within one working day.
            @else
                Our team replies to every enquiry from your account.
            @endif
        </p>
    </li>
</ul>
