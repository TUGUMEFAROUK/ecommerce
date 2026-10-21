<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php($settings = app(\App\Services\SettingsService::class))

        <title>
            @if (filled($title ?? null))
                {{ $title }} — {{ $settings->get('store_name') }}
            @else
                {{ $settings->get('store_name') }}
            @endif
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="flex min-h-screen flex-col bg-gray-50 text-gray-900">
        <a
            href="#main-content"
            class="sr-only rounded bg-gray-900 px-4 py-2 text-white focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50"
        >
            Skip to content
        </a>

        @include('components.partials.site-header')

        <main id="main-content" class="mx-auto w-full max-w-7xl flex-1 px-4 py-8">
            @if (session('status'))
                <div class="mb-6 rounded border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (isset($errors) && $errors->any())
                <div class="mb-6 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>

        @include('components.partials.site-footer')
    </body>
</html>
