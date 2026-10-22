<x-layouts.admin title="Settings">
    <x-admin.page-header title="Settings" description="Store details, content, shipping and inventory defaults." />

    @php
        $logoPath = $settings['store_logo'] ?? '';
        $logoImage = filled($logoPath) && \Illuminate\Support\Facades\Storage::disk('public')->exists($logoPath)
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($logoPath)
            : null;
        $heroImage = \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['hero_image'] ?? '')
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings['hero_image'])
            : null;
        $promoImage = \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['promo_image'] ?? '')
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings['promo_image'])
            : null;
        $newsletterImage = \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['newsletter_image'] ?? '')
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($settings['newsletter_image'])
            : null;
    @endphp

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid max-w-5xl gap-6 lg:grid-cols-2">
            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Store details</h2>

                <div>
                    <label for="store_name" class="admin-label">Store name</label>
                    <input
                        id="store_name"
                        type="text"
                        name="store_name"
                        value="{{ old('store_name', $settings['store_name']) }}"
                        required
                        maxlength="100"
                        class="admin-field"
                    >
                    @error('store_name') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Shown in the storefront header, footer and page titles.</p>
                </div>

                <div>
                    <label for="store_tagline" class="admin-label">Store tagline</label>
                    <input
                        id="store_tagline"
                        type="text"
                        name="store_tagline"
                        value="{{ old('store_tagline', $settings['store_tagline']) }}"
                        maxlength="500"
                        class="admin-field"
                    >
                    @error('store_tagline') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Used in the footer and as the default page description.</p>
                </div>

                <div>
                    <label for="store_logo_upload" class="admin-label">Company logo</label>
                    <input
                        id="store_logo_upload"
                        type="file"
                        name="store_logo_upload"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                    >
                    @error('store_logo_upload') <p class="admin-error">{{ $message }}</p> @enderror

                    @if ($logoImage)
                        <div class="mt-3 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <img src="{{ $logoImage }}" alt="Current company logo" class="h-12 w-auto max-w-[12rem] object-contain">
                            <label class="flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="store_logo_remove" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                Remove this logo
                            </label>
                        </div>
                    @endif

                    <p class="admin-help">Optional. PNG, JPG or WebP, up to 2&nbsp;MB and 2400&nbsp;&times;&nbsp;2400&nbsp;px. Used when you choose the logo as your branding below.</p>
                </div>

                <div>
                    <label for="store_logo_height" class="admin-label">Logo size</label>
                    <select
                        id="store_logo_height"
                        name="store_logo_height"
                        class="admin-field"
                    >
                        @foreach ([24 => 'Small', 36 => 'Medium', 48 => 'Large', 64 => 'Extra large'] as $height => $label)
                            <option value="{{ $height }}" @selected((int) old('store_logo_height', $settings['store_logo_height'] ?? 36) === $height)>
                                {{ $label }} &mdash; {{ $height }} px tall
                            </option>
                        @endforeach
                    </select>
                    @error('store_logo_height') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">
                        The height the logo is drawn at in the header and footer. Kept between
                        {{ \App\Services\SettingsService::MIN_LOGO_HEIGHT }}&nbsp;px and
                        {{ \App\Services\SettingsService::MAX_LOGO_HEIGHT }}&nbsp;px so it never breaks the layout.
                    </p>
                </div>

                <div>
                    <span class="admin-label">Branding shown to customers</span>

                    <div class="grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                            <input
                                type="radio"
                                name="brand_display"
                                value="logo"
                                class="mt-1 h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900"
                                @checked(old('brand_display', $settings['brand_display'] ?? 'name') === 'logo')
                            >
                            <span>
                                <span class="block text-sm font-medium text-slate-900">Company logo</span>
                                <span class="admin-help">Show the logo on its own.</span>
                            </span>
                        </label>

                        <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-200 p-3 has-[:checked]:border-slate-900 has-[:checked]:bg-slate-50">
                            <input
                                type="radio"
                                name="brand_display"
                                value="name"
                                class="mt-1 h-4 w-4 border-slate-300 text-slate-900 focus:ring-slate-900"
                                @checked(old('brand_display', $settings['brand_display'] ?? 'name') !== 'logo')
                            >
                            <span>
                                <span class="block text-sm font-medium text-slate-900">Store name</span>
                                <span class="admin-help">Show the name with its initial tile.</span>
                            </span>
                        </label>
                    </div>

                    @error('brand_display') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">
                        The logo and the store name are never shown together &mdash; pick one for the storefront
                        header, footer and admin panel. Choosing the logo before uploading a file keeps showing
                        the store name.
                    </p>
                </div>

                <div>
                    <label for="store_currency" class="admin-label">Currency code</label>
                    <select
                        id="store_currency"
                        name="store_currency"
                        required
                        class="admin-field"
                    >
                        @foreach (\App\Support\Currency::options() as $code => $currencyName)
                            <option value="{{ $code }}" @selected(old('store_currency', $settings['store_currency']) === $code)>
                                {{ $currencyName }} ({{ $code }})
                            </option>
                        @endforeach
                    </select>
                    @error('store_currency') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">
                        Applied to storefront prices, shipping quotes and new orders. Choose
                        Ugandan Shilling (UGX) to price in shillings &mdash; shillings are shown without decimals.
                        Changing the currency does not convert prices you have already set.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="contact_email" class="admin-label">Contact email</label>
                        <input
                            id="contact_email"
                            type="email"
                            name="contact_email"
                            value="{{ old('contact_email', $settings['contact_email']) }}"
                            maxlength="255"
                            class="admin-field"
                        >
                        @error('contact_email') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="contact_phone" class="admin-label">Contact phone</label>
                        <input
                            id="contact_phone"
                            type="text"
                            name="contact_phone"
                            value="{{ old('contact_phone', $settings['contact_phone']) }}"
                            maxlength="30"
                            class="admin-field"
                        >
                        @error('contact_phone') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="mt-1.5 text-xs text-slate-500">Contact details are shown in the storefront footer and the support panel.</p>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Announcement bar</h2>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="announcement_enabled" value="0">
                    <input
                        id="announcement_enabled"
                        type="checkbox"
                        name="announcement_enabled"
                        value="1"
                        @checked(old('announcement_enabled', $settings['announcement_enabled']) !== '0')
                        class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900"
                    >
                    <label for="announcement_enabled" class="text-sm font-medium text-slate-700">Show the announcement bar</label>
                </div>
                @error('announcement_enabled') <p class="admin-error">{{ $message }}</p> @enderror

                <div>
                    <label for="announcement_text" class="admin-label">Announcement message</label>
                    <input
                        id="announcement_text"
                        type="text"
                        name="announcement_text"
                        value="{{ old('announcement_text', $settings['announcement_text']) }}"
                        maxlength="255"
                        class="admin-field"
                    >
                    @error('announcement_text') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Leave empty to use the default message, which quotes your free-delivery threshold.</p>
                </div>

                <div>
                    <label for="announcement_link" class="admin-label">Announcement link</label>
                    <input
                        id="announcement_link"
                        type="text"
                        name="announcement_link"
                        value="{{ old('announcement_link', $settings['announcement_link']) }}"
                        maxlength="255"
                        placeholder="/products"
                        class="admin-field"
                    >
                    @error('announcement_link') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Optional. Adds a &ldquo;Shop now&rdquo; link to the announcement bar.</p>
                </div>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Homepage hero</h2>

                <div>
                    <label for="hero_eyebrow" class="admin-label">Eyebrow</label>
                    <input
                        id="hero_eyebrow"
                        type="text"
                        name="hero_eyebrow"
                        value="{{ old('hero_eyebrow', $settings['hero_eyebrow']) }}"
                        maxlength="100"
                        class="admin-field"
                    >
                    @error('hero_eyebrow') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="hero_heading" class="admin-label">Headline</label>
                    <input
                        id="hero_heading"
                        type="text"
                        name="hero_heading"
                        value="{{ old('hero_heading', $settings['hero_heading']) }}"
                        maxlength="255"
                        class="admin-field"
                    >
                    @error('hero_heading') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="hero_body" class="admin-label">Supporting text</label>
                    <textarea
                        id="hero_body"
                        name="hero_body"
                        rows="3"
                        maxlength="1000"
                        class="admin-textarea"
                    >{{ old('hero_body', $settings['hero_body']) }}</textarea>
                    @error('hero_body') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="hero_cta_label" class="admin-label">Button label</label>
                        <input
                            id="hero_cta_label"
                            type="text"
                            name="hero_cta_label"
                            value="{{ old('hero_cta_label', $settings['hero_cta_label']) }}"
                            maxlength="100"
                            class="admin-field"
                        >
                        @error('hero_cta_label') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="hero_cta_url" class="admin-label">Button link</label>
                        <input
                            id="hero_cta_url"
                            type="text"
                            name="hero_cta_url"
                            value="{{ old('hero_cta_url', $settings['hero_cta_url']) }}"
                            maxlength="255"
                            placeholder="/products"
                            class="admin-field"
                        >
                        @error('hero_cta_url') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="hero_image_upload" class="admin-label">Hero image</label>
                    <input
                        id="hero_image_upload"
                        type="file"
                        name="hero_image_upload"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                    >
                    @error('hero_image_upload') <p class="admin-error">{{ $message }}</p> @enderror
                    @if ($heroImage)
                <img src="{{ $heroImage }}" alt="Current hero image" class="mt-3 h-32 w-auto rounded-lg border border-slate-200 object-cover">
                <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" name="hero_image_remove" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            Remove this image
                        </label>
                    @endif
                    <p class="admin-help">Optional. Without an image the hero falls back to catalogue photography.</p>
                </div>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Editorial band</h2>

                <div>
                    <label for="promo_eyebrow" class="admin-label">Eyebrow</label>
                    <input
                        id="promo_eyebrow"
                        type="text"
                        name="promo_eyebrow"
                        value="{{ old('promo_eyebrow', $settings['promo_eyebrow']) }}"
                        maxlength="100"
                        class="admin-field"
                    >
                    @error('promo_eyebrow') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo_heading" class="admin-label">Headline</label>
                    <input
                        id="promo_heading"
                        type="text"
                        name="promo_heading"
                        value="{{ old('promo_heading', $settings['promo_heading']) }}"
                        maxlength="255"
                        class="admin-field"
                    >
                    @error('promo_heading') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="promo_body" class="admin-label">Body</label>
                    <textarea
                        id="promo_body"
                        name="promo_body"
                        rows="3"
                        maxlength="1000"
                        class="admin-textarea"
                    >{{ old('promo_body', $settings['promo_body']) }}</textarea>
                    @error('promo_body') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="promo_cta_label" class="admin-label">Button label</label>
                        <input
                            id="promo_cta_label"
                            type="text"
                            name="promo_cta_label"
                            value="{{ old('promo_cta_label', $settings['promo_cta_label']) }}"
                            maxlength="100"
                            class="admin-field"
                        >
                        @error('promo_cta_label') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="promo_cta_url" class="admin-label">Button link</label>
                        <input
                            id="promo_cta_url"
                            type="text"
                            name="promo_cta_url"
                            value="{{ old('promo_cta_url', $settings['promo_cta_url']) }}"
                            maxlength="255"
                            placeholder="/products"
                            class="admin-field"
                        >
                        @error('promo_cta_url') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label for="promo_image_upload" class="admin-label">Band image</label>
                    <input
                        id="promo_image_upload"
                        type="file"
                        name="promo_image_upload"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                    >
                    @error('promo_image_upload') <p class="admin-error">{{ $message }}</p> @enderror
                    @if ($promoImage)
                <img src="{{ $promoImage }}" alt="Current editorial band image" class="mt-3 h-32 w-auto rounded-lg border border-slate-200 object-cover">
                <label class="mt-2 flex items-center gap-2 text-xs text-slate-600">
                    <input type="checkbox" name="promo_image_remove" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                            Remove this image
                        </label>
                    @endif
                </div>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Shipping &amp; returns</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="shipping_flat_fee" class="admin-label">Flat shipping fee</label>
                        <input
                            id="shipping_flat_fee"
                            type="number"
                            name="shipping_flat_fee"
                            value="{{ old('shipping_flat_fee', $settings['shipping_flat_fee']) }}"
                            required
                            min="0"
                            max="999999.99"
                            step="{{ \App\Support\Money::decimals() > 0 ? '0.01' : '1' }}"
                            class="admin-field"
                        >
                        @error('shipping_flat_fee') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="shipping_free_threshold" class="admin-label">Free shipping from</label>
                        <input
                            id="shipping_free_threshold"
                            type="number"
                            name="shipping_free_threshold"
                            value="{{ old('shipping_free_threshold', $settings['shipping_free_threshold']) }}"
                            required
                            min="0"
                            max="99999999.99"
                            step="{{ \App\Support\Money::decimals() > 0 ? '0.01' : '1' }}"
                            class="admin-field"
                        >
                        @error('shipping_free_threshold') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="delivery_estimate" class="admin-label">Delivery estimate</label>
                        <input
                            id="delivery_estimate"
                            type="text"
                            name="delivery_estimate"
                            value="{{ old('delivery_estimate', $settings['delivery_estimate']) }}"
                            maxlength="100"
                            class="admin-field"
                        >
                        @error('delivery_estimate') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="returns_window_days" class="admin-label">Return window (days)</label>
                        <input
                            id="returns_window_days"
                            type="number"
                            name="returns_window_days"
                            value="{{ old('returns_window_days', $settings['returns_window_days']) }}"
                            required
                            min="0"
                            max="365"
                            step="1"
                            class="admin-field"
                        >
                        @error('returns_window_days') <p class="admin-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <p class="mt-1.5 text-xs text-slate-500">These figures drive the checkout totals, the announcement bar, the storefront benefits panel and the published policies.</p>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Newsletter &amp; social</h2>

                <div>
                    <label for="newsletter_heading" class="admin-label">Newsletter heading</label>
                    <input
                        id="newsletter_heading"
                        type="text"
                        name="newsletter_heading"
                        value="{{ old('newsletter_heading', $settings['newsletter_heading']) }}"
                        maxlength="255"
                        class="admin-field"
                    >
                    @error('newsletter_heading') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="newsletter_body" class="admin-label">Newsletter body</label>
                    <textarea
                        id="newsletter_body"
                        name="newsletter_body"
                        rows="3"
                        maxlength="1000"
                        class="admin-textarea"
                    >{{ old('newsletter_body', $settings['newsletter_body']) }}</textarea>
                    @error('newsletter_body') <p class="admin-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="newsletter_image_upload" class="admin-label">Journal image</label>
                    <input
                        id="newsletter_image_upload"
                        type="file"
                        name="newsletter_image_upload"
                        accept="image/jpeg,image/png,image/webp"
                        class="block w-full text-sm text-slate-600 file:mr-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-4 file:py-2 file:text-sm file:font-medium file:text-white hover:file:bg-slate-800"
                    >
                    @error('newsletter_image_upload') <p class="admin-error">{{ $message }}</p> @enderror

                    @if ($newsletterImage)
                        <div class="mt-3 flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3">
                            <img src="{{ $newsletterImage }}" alt="Current journal image" class="h-24 w-auto max-w-[16rem] rounded-lg object-cover">
                            <label class="flex items-center gap-2 text-xs text-slate-600">
                                <input type="checkbox" name="newsletter_image_remove" value="1" class="h-4 w-4 rounded border-slate-300 text-slate-900 focus:ring-slate-900">
                                Remove this image
                            </label>
                        </div>
                    @endif

                    <p class="admin-help">
                        Optional. Shown beside the &ldquo;Join the journal&rdquo; block on the homepage.
                        PNG, JPG or WebP, up to 4&nbsp;MB, between 320&nbsp;&times;&nbsp;240&nbsp;px and
                        2400&nbsp;&times;&nbsp;2400&nbsp;px. Without an image the block falls back to catalogue photography.
                    </p>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'social_facebook' => 'Facebook',
                        'social_instagram' => 'Instagram',
                        'social_x' => 'X',
                        'social_pinterest' => 'Pinterest',
                    ] as $key => $label)
                        <div>
                            <label for="{{ $key }}" class="admin-label">{{ $label }} URL</label>
                            <input
                                id="{{ $key }}"
                                type="url"
                                name="{{ $key }}"
                                value="{{ old($key, $settings[$key]) }}"
                                maxlength="255"
                                placeholder="https://"
                                class="admin-field"
                            >
                            @error($key) <p class="admin-error">{{ $message }}</p> @enderror
                        </div>
                    @endforeach
                </div>

                <p class="mt-1.5 text-xs text-slate-500">Social links only appear in the footer once a URL is saved.</p>
            </section>

            <section class="admin-card space-y-4 p-5">
                <h2 class="text-base font-semibold text-slate-900">Inventory</h2>

                <div>
                    <label for="low_stock_threshold" class="admin-label">Low-stock threshold</label>
                    <input
                        id="low_stock_threshold"
                        type="number"
                        name="low_stock_threshold"
                        value="{{ old('low_stock_threshold', $settings['low_stock_threshold']) }}"
                        required
                        min="0"
                        max="1000000"
                        step="1"
                        class="admin-field w-32"
                    >
                    @error('low_stock_threshold') <p class="admin-error">{{ $message }}</p> @enderror
                    <p class="admin-help">Products at or below this stock level appear on the dashboard, the inventory page and product cards.</p>
                </div>
            </section>
        </div>

        <div class="mt-6">
            <x-admin.button type="submit">Save settings</x-admin.button>
        </div>
    </form>
</x-layouts.admin>
