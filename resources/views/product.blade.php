<x-layouts.shop :title="$product->name">
    <nav aria-label="Breadcrumb" class="mb-6 text-sm text-gray-600">
        <ol class="flex flex-wrap items-center gap-2">
            <li><a href="{{ route('home') }}" class="hover:text-gray-900 hover:underline">Home</a></li>
            <li aria-hidden="true">/</li>
            <li><a href="{{ route('products.index') }}" class="hover:text-gray-900 hover:underline">Products</a></li>
            @if ($product->category !== null)
                <li aria-hidden="true">/</li>
                <li>
                    <a
                        href="{{ route('products.index', ['category' => $product->category->slug]) }}"
                        class="hover:text-gray-900 hover:underline"
                    >
                        {{ $product->category->name }}
                    </a>
                </li>
            @endif
            <li aria-hidden="true">/</li>
            <li aria-current="page" class="font-medium text-gray-900">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="grid gap-8 lg:grid-cols-2">
        <div>
            <x-product-image :product="$product" class="aspect-square rounded-lg border border-gray-200" />

            @if ($product->images->count() > 1)
                <ul class="mt-4 grid grid-cols-4 gap-3" aria-label="More product images">
                    @foreach ($product->images->skip(1) as $image)
                        <li class="overflow-hidden rounded border border-gray-200 bg-gray-100">
                            @if (\Illuminate\Support\Facades\Storage::disk('public')->exists($image->path))
                                <img
                                    src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image->path) }}"
                                    alt="{{ $product->name }} photo {{ $loop->iteration + 1 }}"
                                    loading="lazy"
                                    class="aspect-square w-full object-cover"
                                >
                            @else
                                <img
                                    src="{{ asset('images/product-placeholder.svg') }}"
                                    alt="{{ $product->name }} photo {{ $loop->iteration + 1 }} (no image available)"
                                    loading="lazy"
                                    class="aspect-square w-full object-contain p-2 opacity-80"
                                >
                            @endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>

            <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-600">
                <span>SKU: <span class="font-mono">{{ $product->sku }}</span></span>
                @if ($product->category !== null)
                    <span>Category: {{ $product->category->name }}</span>
                @endif
            </div>

            <p class="mt-4 text-3xl font-semibold text-gray-900">${{ number_format((float) $product->price, 2) }}</p>

            <p class="mt-3">
                @if ($product->inStock())
                    <span class="rounded-full bg-green-100 px-3 py-1 text-sm font-medium text-green-800">
                        In stock ({{ $product->stock_quantity }} available)
                    </span>
                @else
                    <span class="rounded-full bg-gray-100 px-3 py-1 text-sm font-medium text-gray-600">
                        Out of stock
                    </span>
                @endif
            </p>

            @if (filled($product->description))
                <div class="mt-6 leading-relaxed text-gray-700">
                    {!! nl2br(e($product->description)) !!}
                </div>
            @else
                <p class="mt-6 text-gray-500 italic">No description has been written for this product yet.</p>
            @endif

            <div class="mt-8 border-t border-gray-200 pt-6">
                <form
                    method="POST"
                    action="{{ route('products.cart.add', $product) }}"
                    class="flex max-w-xs flex-col gap-3"
                >
                    @csrf

                    <div>
                        <label for="quantity" class="mb-1 block text-sm font-medium text-gray-900">Quantity</label>
                        <input
                            id="quantity"
                            type="number"
                            name="quantity"
                            value="{{ old('quantity', 1) }}"
                            min="1"
                            max="{{ $product->inStock() ? min($product->stock_quantity, 10) : 1 }}"
                            required
                            class="w-24 rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >
                        @error('quantity')
                            <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    @if ($product->inStock())
                        <button
                            type="submit"
                            class="rounded bg-gray-900 px-6 py-3 text-sm font-semibold text-white hover:bg-gray-700"
                        >
                            Add to cart
                        </button>
                    @else
                        <button
                            type="submit"
                            disabled
                            aria-disabled="true"
                            class="cursor-not-allowed rounded bg-gray-300 px-6 py-3 text-sm font-semibold text-gray-500"
                        >
                            Out of stock
                        </button>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-layouts.shop>
