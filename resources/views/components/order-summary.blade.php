@props(['lines', 'quote', 'heading' => 'Order summary'])

<div {{ $attributes->merge(['class' => 'surface p-6 sm:p-7']) }}>
    <h2 class="font-display text-2xl">{{ $heading }}</h2>

    <p class="mt-1 text-sm text-charcoal/55">{{ $lines->sum('quantity') }} {{ Str::plural('item', $lines->sum('quantity')) }}</p>

    <dl class="mt-6 space-y-3 text-sm">
        <div class="flex items-center justify-between">
            <dt class="text-charcoal/65">Subtotal</dt>
            <dd class="text-charcoal">{{ \App\Support\Money::format($quote['subtotal_cents'] / 100) }}</dd>
        </div>

        <div class="flex items-center justify-between">
            <dt class="text-charcoal/65">Shipping</dt>
            <dd class="text-charcoal">
                @if ($quote['shipping_cents'] === 0)
                    Free
                @else
                    {{ \App\Support\Money::format($quote['shipping_cents'] / 100) }}
                @endif
            </dd>
        </div>

        <div class="flex items-center justify-between">
            <dt class="text-charcoal/65">Discount</dt>
            <dd class="text-charcoal">{{ \App\Support\Money::format($quote['discount_cents'] / 100) }}</dd>
        </div>

        <div class="flex items-center justify-between border-t border-sand pt-4 text-base">
            <dt class="font-display text-lg">Total</dt>
            <dd class="font-medium">{{ \App\Support\Money::format($quote['total_cents'] / 100) }}</dd>
        </div>
    </dl>
</div>
