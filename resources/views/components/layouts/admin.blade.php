@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php($settings = app(\App\Services\SettingsService::class))

        <title>
            @if (filled($title ?? null))
                {{ $title }} — {{ $settings->get('store_name') }} admin
            @else
                {{ $settings->get('store_name') }} admin
            @endif
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-100 text-gray-900">
        <a
            href="#admin-main"
            class="sr-only rounded bg-gray-900 px-4 py-2 text-white focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50"
        >
            Skip to content
        </a>

        <div class="lg:flex">
            {{-- Mobile menu: a native disclosure widget, so navigation works without JavaScript. --}}
            <details class="w-full border-b border-gray-700 bg-gray-900 text-white lg:hidden">
                <summary class="cursor-pointer px-4 py-3 text-sm font-medium">Admin menu</summary>
                @include('components.partials.admin-sidebar')
            </details>

            {{-- Desktop sidebar --}}
            <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-gray-900 text-white lg:flex">
                @include('components.partials.admin-sidebar')
            </aside>

            <div class="flex min-h-screen w-full flex-col lg:pl-64">
                <header class="flex flex-wrap items-center gap-4 border-b border-gray-200 bg-white px-4 py-3 sm:px-6">
                    <p class="text-sm font-semibold text-gray-900">{{ $settings->get('store_name') }}</p>

                    <nav aria-label="Account" class="ml-auto flex items-center gap-4 text-sm">
                        <a href="{{ route('home') }}" class="text-gray-700 hover:text-gray-900">View store</a>
                        <a href="{{ route('account.show') }}" class="text-gray-700 hover:text-gray-900">Account</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-gray-700 hover:text-gray-900">Log out</button>
                        </form>
                    </nav>
                </header>

                <main id="admin-main" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
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
            </div>
        </div>
    </body>
</html>
