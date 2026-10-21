<footer class="mt-16 border-t border-gray-200 bg-white">
    <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-8 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p>&copy; {{ date('Y') }} {{ $settings->get('store_name') }}. All rights reserved.</p>

            @if ($settings->get('contact_email') !== '' || $settings->get('contact_phone') !== '')
                <p class="mt-1">
                    @if ($settings->get('contact_email') !== '')
                        <a href="mailto:{{ $settings->get('contact_email') }}" class="hover:text-gray-900">{{ $settings->get('contact_email') }}</a>
                    @endif
                    @if ($settings->get('contact_email') !== '' && $settings->get('contact_phone') !== '')
                        &middot;
                    @endif
                    @if ($settings->get('contact_phone') !== '')
                        {{ $settings->get('contact_phone') }}
                    @endif
                </p>
            @endif
        </div>

        <nav aria-label="Footer">
            <ul class="flex gap-5">
                <li><a href="{{ route('home') }}" class="hover:text-gray-900">Home</a></li>
                <li><a href="{{ route('products.index') }}" class="hover:text-gray-900">Browse products</a></li>
            </ul>
        </nav>
    </div>

    <p class="mx-auto max-w-7xl px-4 pb-6 text-xs text-gray-400">
        Demo storefront &mdash; checkout and payment processing are not yet available.
    </p>
</footer>
