<x-layouts.app :title="'Cart'">
    <h1 class="mb-6 text-xl font-semibold">Your cart</h1>

    @if (session('status'))
        <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->has('cart'))
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            {{ $errors->first('cart') }}
        </div>
    @endif

    @if ($lines->isEmpty())
        <x-empty-state title="Your cart is empty" message="Browse the catalog and add products to your cart.">
            <a href="{{ route('products.index') }}" class="inline-block rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Browse products
            </a>
        </x-empty-state>
    @else
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="min-w-0 flex-1 space-y-4">
                @foreach ($lines as $line)
                    @php($product = $line['product'])

                    <article class="flex flex-col gap-4 rounded border border-gray-200 bg-white p-4 sm:flex-row sm:items-center">
                        <div class="h-20 w-20 shrink-0 overflow-hidden rounded">
                            <x-product-image :product="$product" class="h-full w-full" />
                        </div>

                        <div class="min-w-0 flex-1">
                            <h2 class="text-sm font-medium text-gray-900">
                                <a href="{{ route('products.show', $product) }}" class="hover:underline">
                                    {{ $product->name }}
                                </a>
                            </h2>
                            <p class="mt-0.5 text-xs text-gray-500">SKU {{ $product->sku }}</p>
                            <p class="mt-1 text-sm text-gray-600">${{ $product->price }} each</p>

                            @if ($problems[$product->getKey()] ?? null)
                                <p class="mt-1 text-xs font-medium text-red-600" role="alert">
                                    {{ $problems[$product->getKey()] }}
                                </p>
                            @endif
                        </div>

                        <div class="flex items-center gap-4">
                            @if ($product->status === \App\Enums\ProductStatus::Active && $product->stock_quantity >= 1)
                                <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')

                                    <label for="quantity-{{ $product->getKey() }}" class="sr-only">
                                        Quantity for {{ $product->name }}
                                    </label>
                                    <input
                                        id="quantity-{{ $product->getKey() }}"
                                        type="number"
                                        name="quantity"
                                        value="{{ $line['quantity'] }}"
                                        min="1"
                                        max="{{ min($product->stock_quantity, 10) }}"
                                        required
                                        class="w-20 rounded border border-gray-300 px-2 py-1.5 text-sm focus:border-gray-900 focus:outline-none"
                                    >
                                    <button
                                        type="submit"
                                        class="rounded border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100"
                                    >
                                        Update
                                    </button>
                                </form>
                            @endif

                            <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="text-sm text-red-600 hover:underline"
                                    aria-label="Remove {{ $product->name }} from cart"
                                >
                                    Remove
                                </button>
                            </form>
                        </div>

                        <p class="text-right text-sm font-semibold text-gray-900 sm:w-24">
                            ${{ $lineTotals[$product->getKey()] }}
                        </p>
                    </article>
                @endforeach

                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('products.index') }}" class="text-sm text-gray-900 underline">
                        &larr; Continue shopping
                    </a>

                    <form method="POST" action="{{ route('cart.clear') }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-sm text-red-600 hover:underline">
                            Clear cart
                        </button>
                    </form>
                </div>
            </div>

            <div class="w-full shrink-0 lg:w-80">
                <x-order-summary :lines="$lines" :quote="$quote" />

                <a
                    href="{{ route('checkout.show') }}"
                    class="mt-4 block rounded bg-gray-900 px-4 py-3 text-center text-sm font-semibold text-white hover:bg-gray-700"
                >
                    Proceed to checkout
                </a>
            </div>
        </div>
    @endif
</x-layouts.app>
