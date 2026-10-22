@props(['heading' => null, 'body' => null])

@php($settings = app(\App\Services\SettingsService::class))

<div {{ $attributes->merge(['class' => 'max-w-xl']) }}>
    @if (filled($heading))
        <h2 id="newsletter-heading" class="font-display text-3xl sm:text-4xl">{{ $heading }}</h2>
    @endif

    @if (filled($body))
        <p class="mt-4 max-w-lg text-base leading-relaxed text-charcoal/70">{{ $body }}</p>
    @endif

    <form method="POST" action="{{ route('newsletter.subscribe') }}" class="mt-7 max-w-lg" novalidate>
        @csrf

        <label for="newsletter-email" class="field-label">Email address</label>

        <div class="flex flex-col gap-3 sm:flex-row">
            <input
                id="newsletter-email"
                type="email"
                name="newsletter_email"
                value="{{ old('newsletter_email') }}"
                placeholder="you@example.com"
                autocomplete="email"
                maxlength="255"
                required
                aria-describedby="newsletter-feedback"
                class="field flex-1 {{ $errors->has('newsletter_email') ? 'border-clay' : '' }}"
            >
            <button type="submit" class="btn btn-primary shrink-0">Subscribe</button>
        </div>

        <div id="newsletter-feedback" class="mt-3 min-h-[1.25rem]">
            @if (session('newsletter_status'))
                <p role="status" class="flex items-start gap-2 text-sm text-olive">
                    <span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-olive" aria-hidden="true"></span>
                    {{ session('newsletter_status') }}
                </p>
            @elseif ($errors->has('newsletter_email'))
                <p role="alert" class="text-sm text-clay">{{ $errors->first('newsletter_email') }}</p>
            @else
                <p class="text-xs text-charcoal/50">We respect your inbox. Unsubscribe at any time.</p>
            @endif
        </div>
    </form>
</div>
