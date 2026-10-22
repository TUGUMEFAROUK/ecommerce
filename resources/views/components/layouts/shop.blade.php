@props(['title' => null, 'description' => null])

@php($settings = app(\App\Services\SettingsService::class))

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            @if (filled($title ?? null))
                {{ $title }} — {{ $settings->get('store_name') }}
            @else
                {{ $settings->get('store_name') }}
            @endif
        </title>

        @if (filled($description ?? null))
            <meta name="description" content="{{ $description }}">
        @else
            <meta name="description" content="{{ $settings->get('store_tagline') }}">
        @endif

        <meta name="theme-color" content="#F7F5F0">

        <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
        <link
            rel="preload"
            as="font"
            type="font/woff2"
            href="https://fonts.bunny.net/cormorant-garamond/files/cormorant-garamond-latin-400-normal.woff2"
            crossorigin
        >
        <link
            rel="preload"
            as="font"
            type="font/woff2"
            href="https://fonts.bunny.net/jost/files/jost-latin-400-normal.woff2"
            crossorigin
        >
        <link
            rel="stylesheet"
            href="https://fonts.bunny.net/css?family=cormorant-garamond:400,500,600,700|jost:300,400,500,600&display=swap"
        >

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-ivory text-charcoal">
        <a
            href="#main-content"
            class="sr-only rounded-sm bg-charcoal px-4 py-2 text-sm text-white focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50"
        >
            Skip to content
        </a>

        @include('components.partials.announcement-bar')

        @include('components.partials.site-header')

        <main id="main-content" class="flex-1 pb-20">
            @if (session('status'))
                <div class="shell pt-6">
                    <div class="flex items-start gap-3 rounded-sm border border-olive/30 bg-mist px-4 py-3 text-sm text-charcoal" role="status">
                        <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-olive" aria-hidden="true"></span>
                        <p>{{ session('status') }}</p>
                    </div>
                </div>
            @endif

            @if (isset($errors) && $errors->any() && ! $errors->has('newsletter_email'))
                <div class="shell pt-6">
                    <div class="rounded-sm border border-clay/30 bg-mist px-4 py-3 text-sm text-clay" role="alert">
                        <ul class="list-disc space-y-1 pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            {{ $slot }}
        </main>

        @include('components.partials.site-footer')
    </body>
</html>
