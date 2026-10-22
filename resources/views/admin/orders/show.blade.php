<x-layouts.admin :title="'Order #'.$order->id">
    <x-admin.page-header :title="'Order #'.$order->id" :description="'Placed '.$order->created_at?->format('M j, Y H:i')">
        <x-admin.button variant="secondary" :href="route('admin.orders.index')">Back to orders</x-admin.button>
    </x-admin.page-header>

    <div class="mb-6 flex flex-wrap items-center gap-3">
        <x-admin.status-badge :status="$order->status" />
        <span class="text-sm font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($order->total, $order->currency) }}</span>
        <span class="text-sm text-slate-500">{{ $order->items->sum('quantity') }} {{ Str::plural('item', $order->items->sum('quantity')) }}</span>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <section class="admin-card p-5">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Customer</h2>

            <dl class="divide-y divide-slate-100 text-sm">
                <div class="py-3 first:pt-0">
                    <dt class="text-slate-500">Account</dt>
                    <dd class="font-medium text-slate-900">
                        @if ($order->user === null)
                            Guest checkout
                        @elseif ($order->user->isAdmin())
                            {{ $order->user->name }} ({{ $order->user->email }})
                        @else
                            <a href="{{ route('admin.customers.show', $order->user) }}" class="hover:underline">
                                {{ $order->user->name }} ({{ $order->user->email }})
                            </a>
                        @endif
                    </dd>
                </div>
                <div class="py-3">
                    <dt class="text-slate-500">Contact</dt>
                    <dd class="font-medium text-slate-900">{{ $order->contact_name }}</dd>
                    <dd class="text-slate-600">{{ $order->contact_email }}</dd>
                    <dd class="text-slate-600">{{ $order->contact_phone }}</dd>
                </div>
                <div class="py-3 last:pb-0">
                    <dt class="text-slate-500">Shipping address</dt>
                    <dd class="font-medium text-slate-900">{{ $order->shipping_line1 }}</dd>
                    @if (filled($order->shipping_line2))
                        <dd class="text-slate-600">{{ $order->shipping_line2 }}</dd>
                    @endif
                    <dd class="text-slate-600">{{ $order->shipping_city }}{{ filled($order->shipping_state) ? ', '.$order->shipping_state : '' }} {{ $order->shipping_postal_code }}</dd>
                    <dd class="text-slate-600">{{ $order->shipping_country }}</dd>
                </div>
            </dl>
        </section>

        <section class="admin-card p-5">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Status</h2>

            @if ($allowedTransitions === [])
                <p class="text-sm text-slate-500">
                    This order is in a final state ({{ Str::headline($order->status->value) }}); no further status changes are available.
                </p>
            @else
                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="status" class="admin-label">Move to</label>
                        <select id="status" name="status" required class="admin-field">
                            @foreach ($allowedTransitions as $transition)
                                <option value="{{ $transition }}">{{ Str::headline($transition) }}</option>
                            @endforeach
                        </select>
                        @error('status') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="notes" class="admin-label">Notes (optional)</label>
                        <textarea
                            id="notes"
                            name="notes"
                            rows="3"
                            maxlength="1000"
                            placeholder="Tracking number, internal note…"
                            class="admin-textarea"
                        >{{ old('notes') }}</textarea>
                        @error('notes') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <x-admin.button type="submit">Update status</x-admin.button>
                </form>
            @endif

            <h3 class="mb-3 mt-6 text-sm font-semibold text-slate-900">History</h3>

            @if ($order->statusHistory->isEmpty())
                <p class="text-sm text-slate-500">No status changes have been recorded yet.</p>
            @else
                <ol class="space-y-4 text-sm">
                    @foreach ($order->statusHistory as $entry)
                        <li class="relative border-l-2 border-slate-200 pl-4">
                            <span class="absolute -left-[5px] top-1.5 h-2 w-2 rounded-full bg-slate-300"></span>
                            <p class="font-medium text-slate-900">
                                {{ Str::headline($entry->from_status->value) }}
                                &rarr;
                                {{ Str::headline($entry->to_status->value) }}
                            </p>
                            <p class="text-xs text-slate-500">
                                {{ $entry->user?->name ?? 'System' }} &middot; {{ $entry->created_at?->format('M j, Y H:i') }}
                            </p>
                            @if (filled($entry->notes))
                                <p class="mt-1 text-xs text-slate-600">{{ $entry->notes }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            @endif
        </section>

        <section class="admin-card p-5">
            <h2 class="mb-4 text-base font-semibold text-slate-900">Payment</h2>

            @if ($order->payments->isEmpty())
                <p class="text-sm text-slate-500">No payments recorded for this order.</p>
            @else
                <ul class="divide-y divide-slate-100 text-sm">
                    @foreach ($order->payments as $payment)
                        <li class="py-3 first:pt-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium uppercase text-slate-900">{{ $payment->provider }}</span>
                                <x-admin.status-badge :status="$payment->status" />
                            </div>
                            <p class="mt-1 text-slate-600">{{ \App\Support\Money::formatWithCode($payment->amount, $payment->currency) }}</p>
                            @if (filled($payment->provider_transaction_id))
                                <p class="mt-1 break-all text-xs text-slate-500">Transaction ID: {{ $payment->provider_transaction_id }}</p>
                            @endif
                            <p class="mt-1 text-xs text-slate-500">
                                {{ $payment->paid_at !== null ? 'Paid '.$payment->paid_at->format('M j, Y H:i') : 'Not yet paid' }}
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </div>

    <section class="admin-card mt-6">
        <div class="border-b border-slate-100 px-5 py-4">
            <h2 class="text-base font-semibold text-slate-900">Items</h2>
            <p class="text-sm text-slate-500">The products in this order with their captured prices.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-slate-100">
                        <th class="admin-th">Product</th>
                        <th class="admin-th">SKU</th>
                        <th class="admin-th text-right">Unit price</th>
                        <th class="admin-th text-right">Qty</th>
                        <th class="admin-th text-right">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                        <tr class="admin-row">
                            <td class="admin-td font-medium text-slate-900">{{ $item->product_name }}</td>
                            <td class="admin-td">{{ $item->product_sku }}</td>
                            <td class="admin-td text-right">{{ \App\Support\Money::formatWithCode($item->unit_price, $order->currency) }}</td>
                            <td class="admin-td text-right">{{ $item->quantity }}</td>
                            <td class="admin-td text-right font-medium text-slate-900">{{ \App\Support\Money::formatWithCode($item->line_total, $order->currency) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="border-t border-slate-100">
                        <td colspan="4" class="px-5 py-4 text-right text-slate-500">Total</td>
                        <td class="px-5 py-4 text-right text-base font-semibold text-slate-900">
                            {{ \App\Support\Money::formatWithCode($order->total, $order->currency) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </section>
</x-layouts.admin>
