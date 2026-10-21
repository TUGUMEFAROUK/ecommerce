<x-layouts.admin title="Settings">
    <x-admin.page-header title="Settings" />

    <form method="POST" action="{{ route('admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="grid max-w-4xl gap-6 lg:grid-cols-2">
            <section class="space-y-4 rounded border border-gray-200 bg-white p-6">
                <h2 class="text-lg font-semibold">Store details</h2>

                <div>
                    <label for="store_name" class="mb-1 block text-sm font-medium text-gray-700">Store name</label>
                    <input
                        id="store_name"
                        type="text"
                        name="store_name"
                        value="{{ old('store_name', $settings['store_name']) }}"
                        required
                        maxlength="100"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                    >
                    @error('store_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Shown in the storefront header, footer and page titles.</p>
                </div>

                <div>
                    <label for="store_currency" class="mb-1 block text-sm font-medium text-gray-700">Currency code</label>
                    <input
                        id="store_currency"
                        type="text"
                        name="store_currency"
                        value="{{ old('store_currency', $settings['store_currency']) }}"
                        required
                        minlength="3"
                        maxlength="3"
                        class="w-32 rounded border border-gray-300 px-3 py-2 text-sm uppercase focus:border-gray-900 focus:outline-none"
                    >
                    @error('store_currency') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-500">Three-letter ISO code, e.g. USD or EUR. Applied to new orders.</p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="contact_email" class="mb-1 block text-sm font-medium text-gray-700">Contact email</label>
                        <input
                            id="contact_email"
                            type="email"
                            name="contact_email"
                            value="{{ old('contact_email', $settings['contact_email']) }}"
                            maxlength="255"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >
                        @error('contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_phone" class="mb-1 block text-sm font-medium text-gray-700">Contact phone</label>
                        <input
                            id="contact_phone"
                            type="text"
                            name="contact_phone"
                            value="{{ old('contact_phone', $settings['contact_phone']) }}"
                            maxlength="30"
                            class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >
                        @error('contact_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="text-xs text-gray-500">Contact details are shown in the storefront footer.</p>
            </section>

            <div class="space-y-6">
                <section class="space-y-4 rounded border border-gray-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Shipping</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="shipping_flat_fee" class="mb-1 block text-sm font-medium text-gray-700">Flat shipping fee</label>
                            <input
                                id="shipping_flat_fee"
                                type="number"
                                name="shipping_flat_fee"
                                value="{{ old('shipping_flat_fee', $settings['shipping_flat_fee']) }}"
                                required
                                min="0"
                                max="999.99"
                                step="0.01"
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_flat_fee') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="shipping_free_threshold" class="mb-1 block text-sm font-medium text-gray-700">Free shipping from</label>
                            <input
                                id="shipping_free_threshold"
                                type="number"
                                name="shipping_free_threshold"
                                value="{{ old('shipping_free_threshold', $settings['shipping_free_threshold']) }}"
                                required
                                min="0"
                                max="99999.99"
                                step="0.01"
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_free_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <p class="text-xs text-gray-500">Carts below the free-shipping threshold are charged the flat fee. These values drive the checkout totals.</p>
                </section>

                <section class="space-y-4 rounded border border-gray-200 bg-white p-6">
                    <h2 class="text-lg font-semibold">Inventory</h2>

                    <div>
                        <label for="low_stock_threshold" class="mb-1 block text-sm font-medium text-gray-700">Low-stock threshold</label>
                        <input
                            id="low_stock_threshold"
                            type="number"
                            name="low_stock_threshold"
                            value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                            required
                            min="0"
                            max="1000000"
                            step="1"
                            class="w-32 rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                        >
                        @error('low_stock_threshold') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-gray-500">Products at or below this stock level appear on the dashboard and inventory pages.</p>
                    </div>
                </section>
            </div>
        </div>

        <div class="mt-6">
            <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">
                Save settings
            </button>
        </div>
    </form>
</x-layouts.admin>
