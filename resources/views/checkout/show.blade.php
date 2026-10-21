<x-layouts.app :title="'Checkout'">
    <h1 class="mb-6 text-xl font-semibold">Checkout</h1>

    @if ($errors->any())
        <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
            <p class="font-medium">Please fix the following before placing your order:</p>
            <ul class="mt-1 list-inside list-disc">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}">
        @csrf

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start">
            <div class="min-w-0 flex-1 space-y-6">
                <section class="rounded border border-gray-200 bg-white p-5">
                    <h2 class="text-lg font-semibold text-gray-900">Contact information</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="contact_name" class="mb-1 block text-sm font-medium text-gray-900">Full name</label>
                            <input
                                id="contact_name"
                                type="text"
                                name="contact_name"
                                value="{{ old('contact_name', auth()->user()->name) }}"
                                maxlength="255"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('contact_name')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_email" class="mb-1 block text-sm font-medium text-gray-900">Email</label>
                            <input
                                id="contact_email"
                                type="email"
                                name="contact_email"
                                value="{{ old('contact_email', auth()->user()->email) }}"
                                maxlength="255"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('contact_email')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="contact_phone" class="mb-1 block text-sm font-medium text-gray-900">Phone</label>
                            <input
                                id="contact_phone"
                                type="tel"
                                name="contact_phone"
                                value="{{ old('contact_phone') }}"
                                maxlength="30"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('contact_phone')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded border border-gray-200 bg-white p-5">
                    <h2 class="text-lg font-semibold text-gray-900">Shipping address</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="shipping_line1" class="mb-1 block text-sm font-medium text-gray-900">Address line 1</label>
                            <input
                                id="shipping_line1"
                                type="text"
                                name="shipping_line1"
                                value="{{ old('shipping_line1') }}"
                                maxlength="255"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_line1')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="shipping_line2" class="mb-1 block text-sm font-medium text-gray-900">Address line 2 <span class="font-normal text-gray-500">(optional)</span></label>
                            <input
                                id="shipping_line2"
                                type="text"
                                name="shipping_line2"
                                value="{{ old('shipping_line2') }}"
                                maxlength="255"
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_line2')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_city" class="mb-1 block text-sm font-medium text-gray-900">City</label>
                            <input
                                id="shipping_city"
                                type="text"
                                name="shipping_city"
                                value="{{ old('shipping_city') }}"
                                maxlength="255"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_city')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_state" class="mb-1 block text-sm font-medium text-gray-900">State / province</label>
                            <input
                                id="shipping_state"
                                type="text"
                                name="shipping_state"
                                value="{{ old('shipping_state') }}"
                                maxlength="100"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_state')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_postal_code" class="mb-1 block text-sm font-medium text-gray-900">Postal code</label>
                            <input
                                id="shipping_postal_code"
                                type="text"
                                name="shipping_postal_code"
                                value="{{ old('shipping_postal_code') }}"
                                maxlength="20"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_postal_code')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="shipping_country" class="mb-1 block text-sm font-medium text-gray-900">Country</label>
                            <input
                                id="shipping_country"
                                type="text"
                                name="shipping_country"
                                value="{{ old('shipping_country') }}"
                                maxlength="100"
                                required
                                class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-gray-900 focus:outline-none"
                            >
                            @error('shipping_country')
                                <p class="mt-1 text-xs text-red-600" role="alert">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="rounded border border-gray-200 bg-white p-5">
                    <h2 class="text-lg font-semibold text-gray-900">Payment method</h2>

                    <label class="mt-4 flex cursor-pointer items-start gap-3 rounded border border-gray-900 p-4">
                        <input
                            type="radio"
                            name="payment_method"
                            value="cod"
                            {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                            required
                            class="mt-1"
                        >
                        <span>
                            <span class="font-medium text-gray-900">Cash on delivery</span>
                            <span class="block text-sm text-gray-600">
                                Pay with cash when your order arrives. No online payment is taken at this time.
                            </span>
                        </span>
                    </label>

                    @if ($showFlutterwave)
                        <label class="mt-3 flex cursor-pointer items-start gap-3 rounded border border-gray-300 p-4">
                            <input
                                type="radio"
                                name="payment_method"
                                value="flutterwave"
                                {{ old('payment_method') === 'flutterwave' ? 'checked' : '' }}
                                required
                                class="mt-1"
                            >
                            <span>
                                <span class="font-medium text-gray-900">Card &amp; online payments</span>
                                <span class="block text-sm text-gray-600">
                                    Pay securely with a card, bank transfer, or mobile money through Flutterwave.
                                    You will be redirected to their payment page to complete checkout.
                                </span>
                            </span>
                        </label>
                    @else
                        <p class="mt-3 text-xs text-gray-500">
                            Online card payments are not yet available in this store.
                        </p>
                    @endif

                    @error('payment_method')
                        <p class="mt-2 text-xs text-red-600" role="alert">{{ $message }}</p>
                    @enderror
                </section>
            </div>

            <div class="w-full shrink-0 lg:w-80">
                <x-order-summary :lines="$lines" :quote="$quote" heading="Your order" />

                <button
                    type="submit"
                    class="mt-4 w-full rounded bg-gray-900 px-4 py-3 text-sm font-semibold text-white hover:bg-gray-700"
                >
                    Place order &mdash; ${{ $quote['total'] }}
                </button>

                <a href="{{ route('cart.index') }}" class="mt-3 block text-center text-sm text-gray-900 underline">
                    &larr; Back to cart
                </a>
            </div>
        </div>
    </form>
</x-layouts.app>
