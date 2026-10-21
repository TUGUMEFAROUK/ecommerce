<x-layouts.admin :title="'Order #'.$order->id">
    <x-admin.page-header :title="'Order #'.$order->id">
        <a href="{{ route('admin.orders.index') }}" class="rounded border border-gray-300 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            Back to orders
        </a>
    </x-admin.page-header>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium capitalize">{{ $order->status->value }}</span>
        <span class="text-sm text-gray-600">Placed {{ $order->created_at?->format('M j, Y H:i') }}</span>
        <span class="text-sm font-semibold">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">Customer</h2>

            <dl class="divide-y divide-gray-100 text-sm">
                <div class="py-2">
                    <dt class="text-gray-600">Account</dt>
                    <dd class="font-medium">
                        @if ($order->user === null)
                            —
                        @elseif ($order->user->isAdmin())
                            {{ $order->user->name }} ({{ $order->user->email }})
                        @else
                            <a href="{{ route('admin.customers.show', $order->user) }}" class="hover:underline">
                                {{ $order->user->name }} ({{ $order->user->email }})
                            </a>
                        @endif
                    </dd>
                </div>
                <div class="py-2">
                    <dt class="text-gray-600">Contact</dt>
                    <dd class="font-medium">{{ $order->contact_name }}</dd>
                    <dd>{{ $order->contact_email }}</dd>
                    <dd>{{ $order->contact_phone }}</dd>
                </div>
                <div class="py-2">
                    <dt class="text-gray-600">Shipping address</dt>
                    <dd class="font-medium">{{ $order->shipping_line1 }}</dd>
                    @if (filled($order->shipping_line2))
                        <dd>{{ $order->shipping_line2 }}</dd>
                    @endif
                    <dd>{{ $order->shipping_city }}{{ filled($order->shipping_state) ? ', '.$order->shipping_state : '' }} {{ $order->shipping_postal_code }}</dd>
                    <dd>{{ $order->shipping_country }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">Status</h2>

            @if ($allowedTransitions === [])
                <p class="text-sm text-gray-600">
                    This order is in a final state ({{ Str::headline($order->status->value) }}); no further status changes are available.
                </p>
            @else
                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="status" class="mb-1 block text-sm font-medium text-gray-700">Move to</label>
                        <select
                            id="status"
                            name="status"
                            required
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >
                            @foreach ($allowedTransitions as $transition)
                                <option value="{{ $transition }}">{{ Str::headline($transition) }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="mb-1 block text-sm font-medium text-gray-700">Notes (optional)</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="1000"
                            placeholder="Tracking number, internal note…"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                        Update status
                    </button>
                </form>
            @endif

            <h3 class="mb-2 mt-6 text-sm font-semibold text-gray-900">History</h3>

            @if ($order->statusHistory->isEmpty())
                <p class="text-sm text-gray-600">No status changes have been recorded yet.</p>
            @else
                <ol class="space-y-3 text-sm">
                    @foreach ($order->statusHistory as $entry)
                        <li class="border-l-2 border-gray-200 pl-3">
                            <p class="font-medium">
                                {{ Str::headline($entry->from_status->value) }}
                                &rarr;
                                {{ Str::headline($entry->to_status->value) }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ $entry->user?->name ?? 'System' }} &middot; {{ $entry->created_at?->format('M j, Y H:i') }}
                            </p>
                            @if (filled($entry->notes))
                                <p class="mt-1 text-xs text-gray-600">{{ $entry->notes }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="rounded border border-gray-200 bg-white p-6">
            <h2 class="mb-4 text-lg font-semibold">Payment</h2>

            @if ($order->payments->isEmpty())
                <p class="text-sm text-gray-600">No payments recorded for this order.</p>
            @else
                <ul class="divide-y divide-gray-100 text-sm">
                    @foreach ($order->payments as $payment)
                        <li class="py-2">
                            <div class="flex items-center justify-between">
                                <span class="font-medium uppercase">{{ $payment->provider }}</span>
                                <span class="capitalize">{{ $payment->status->value }}</span>
                            </div>
                            <p class="text-gray-600">{{ $payment->currency }} {{ number_format((float) $payment->amount, 2) }}</p>
                            @if (filled($payment->provider_transaction_id))
                                <p class="text-xs text-gray-500">Transaction ID: {{ $payment->provider_transaction_id }}</p>
                            @endif
                            <p class="text-xs text-gray-500">
                                {{ $payment->paid_at !== null ? 'Paid '.$payment->paid_at->format('M j, Y H:i') : 'Not yet paid' }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="mt-6 rounded border border-gray-200 bg-white p-6">
        <h2 class="mb-4 text-lg font-semibold">Items</h2>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-gray-200 text-gray-600">
                    <tr>
                        <th class="py-2 pr-4 font-medium">Product</th>
                        <th class="py-2 pr-4 font-medium">SKU</th>
                        <th class="py-2 pr-4 text-right font-medium">Unit price</th>
                        <th class="py-2 pr-4 text-right font-medium">Qty</th>
                        <th class="py-2 text-right font-medium">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="border-b border-gray-100 last:border-0">
                            <td class="py-2 pr-4">{{ $item->product_name }}</td>
                            <td class="py-2 pr-4 text-gray-600">{{ $item->product_sku }}</td>
                            <td class="py-2 pr-4 text-right">{{ number_format((float) $item->unit_price, 2) }}</td>
                            <td class="py-2 pr-4 text-right">{{ $item->quantity }}</td>
                            <td class="py-2 text-right font-medium">{{ number_format((float) $item->line_total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="py-3 pr-4 text-right text-gray-600">Total</td>
                        <td class="py-3 text-right text-base font-semibold">
                            {{ $order->currency }} {{ number_format((float) $order->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
</x-layouts.admin>
