@props(['lines', 'quote', 'heading' => 'Order summary'])

<div {{ $attributes->merge(['class' => 'rounded border border-gray-200 bg-white p-5']) }}>
    <h2 class="text-lg font-semibold text-gray-900">{{ $heading }}</h2>

    <p class="mt-1 text-sm text-gray-600">
        {{ $lines->sum('quantity') }} item(s)
    </p>

    <dl class="mt-4 space-y-2 text-sm">
        <div class="flex justify-between">
            <dt class="text-gray-600">Subtotal</dt>
            <dd class="font-medium text-gray-900">${{ $quote['subtotal'] }}</dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-600">Shipping</dt>
            <dd class="font-medium text-gray-900">
                @if ($quote['shipping_cents'] === 0)
                    Free
                @else
                    ${{ $quote['shipping'] }}
                @endif
            </dd>
        </div>

        <div class="flex justify-between">
            <dt class="text-gray-600">Discount</dt>
            <dd class="font-medium text-gray-900">${{ $quote['discount'] }}</dd>
        </div>

        <div class="flex justify-between border-t border-gray-200 pt-3 text-base font-semibold">
            <dt>Total</dt>
            <dd>${{ $quote['total'] }}</dd>
        </div>
    </dl>
</div>
