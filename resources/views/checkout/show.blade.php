<x-layouts.shop title="Checkout">
    <div class="shell pt-10 lg:pt-14">
        <p class="eyebrow">Secure checkout</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">Checkout</h1>

        @if ($errors->any())
            <div class="mt-8 rounded-sm border border-clay/30 bg-clay/10 px-4 py-3 text-sm text-charcoal" role="alert">
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

            <div class="mt-10 flex flex-col gap-10 lg:flex-row lg:items-start lg:gap-16">
                <div class="min-w-0 flex-1 space-y-8">
                    <section class="surface p-6 sm:p-8">
                        <h2 class="font-display text-2xl">Contact information</h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="contact_name" class="field-label">Full name</label>
                                <input
                                    id="contact_name"
                                    type="text"
                                    name="contact_name"
                                    value="{{ old('contact_name', auth()->user()->name) }}"
                                    maxlength="255"
                                    required
                                    class="field"
                                >
                                @error('contact_name')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact_email" class="field-label">Email</label>
                                <input
                                    id="contact_email"
                                    type="email"
                                    name="contact_email"
                                    value="{{ old('contact_email', auth()->user()->email) }}"
                                    maxlength="255"
                                    required
                                    class="field"
                                >
                                @error('contact_email')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="contact_phone" class="field-label">Phone</label>
                                <input
                                    id="contact_phone"
                                    type="tel"
                                    name="contact_phone"
                                    value="{{ old('contact_phone') }}"
                                    maxlength="30"
                                    required
                                    class="field"
                                >
                                @error('contact_phone')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="surface p-6 sm:p-8">
                        <h2 class="font-display text-2xl">Shipping address</h2>

                        <div class="mt-6 grid gap-5 sm:grid-cols-2">
                            <div class="sm:col-span-2">
                                <label for="shipping_line1" class="field-label">Address line 1</label>
                                <input
                                    id="shipping_line1"
                                    type="text"
                                    name="shipping_line1"
                                    value="{{ old('shipping_line1') }}"
                                    maxlength="255"
                                    required
                                    class="field"
                                >
                                @error('shipping_line1')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="shipping_line2" class="field-label">
                                    Address line 2 <span class="normal-case tracking-normal text-charcoal/50">(optional)</span>
                                </label>
                                <input
                                    id="shipping_line2"
                                    type="text"
                                    name="shipping_line2"
                                    value="{{ old('shipping_line2') }}"
                                    maxlength="255"
                                    class="field"
                                >
                                @error('shipping_line2')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_city" class="field-label">City</label>
                                <input
                                    id="shipping_city"
                                    type="text"
                                    name="shipping_city"
                                    value="{{ old('shipping_city') }}"
                                    maxlength="255"
                                    required
                                    class="field"
                                >
                                @error('shipping_city')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_state" class="field-label">State / province</label>
                                <input
                                    id="shipping_state"
                                    type="text"
                                    name="shipping_state"
                                    value="{{ old('shipping_state') }}"
                                    maxlength="100"
                                    required
                                    class="field"
                                >
                                @error('shipping_state')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_postal_code" class="field-label">Postal code</label>
                                <input
                                    id="shipping_postal_code"
                                    type="text"
                                    name="shipping_postal_code"
                                    value="{{ old('shipping_postal_code') }}"
                                    maxlength="20"
                                    required
                                    class="field"
                                >
                                @error('shipping_postal_code')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="shipping_country" class="field-label">Country</label>
                                <input
                                    id="shipping_country"
                                    type="text"
                                    name="shipping_country"
                                    value="{{ old('shipping_country') }}"
                                    maxlength="100"
                                    required
                                    class="field"
                                >
                                @error('shipping_country')
                                    <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="surface p-6 sm:p-8">
                        <h2 class="font-display text-2xl">Payment method</h2>

                        <label class="mt-6 flex cursor-pointer items-start gap-3 rounded-sm border border-charcoal bg-mist p-4">
                            <input
                                type="radio"
                                name="payment_method"
                                value="cod"
                                {{ old('payment_method', 'cod') === 'cod' ? 'checked' : '' }}
                                required
                                class="mt-1"
                            >
                            <span>
                                <span class="font-medium text-charcoal">Cash on delivery</span>
                                <span class="block text-sm text-charcoal/65">
                                    Pay with cash when your order arrives. No online payment is taken at this time.
                                </span>
                            </span>
                        </label>

                        @if ($showFlutterwave)
                            <label class="mt-3 flex cursor-pointer items-start gap-3 rounded-sm border border-sand bg-white p-4">
                                <input
                                    type="radio"
                                    name="payment_method"
                                    value="flutterwave"
                                    {{ old('payment_method') === 'flutterwave' ? 'checked' : '' }}
                                    required
                                    class="mt-1"
                                >
                                <span>
                                    <span class="font-medium text-charcoal">Card &amp; online payments</span>
                                    <span class="block text-sm text-charcoal/65">
                                        Pay securely with a card, bank transfer, or mobile money through Flutterwave.
                                        You will be redirected to their payment page to complete checkout.
                                    </span>
                                </span>
                            </label>
                        @else
                            <p class="mt-3 text-xs text-charcoal/55">
                                Online card payments are not yet available in this store.
                            </p>
                        @endif

                        @error('payment_method')
                            <p class="mt-2 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </section>
                </div>

                <div class="w-full shrink-0 lg:w-96">
                    <x-order-summary :lines="$lines" :quote="$quote" heading="Your order" />

                    <button type="submit" class="btn btn-primary mt-6 w-full">
                        Place order &mdash; {{ \App\Support\Money::format($quote['total_cents'] / 100) }}
                    </button>

                    <a href="{{ route('cart.index') }}" class="link-quiet mt-4 block text-center text-sm">
                        &larr; Back to cart
                    </a>
                </div>
            </div>
        </form>
    </div>
</x-layouts.shop>
