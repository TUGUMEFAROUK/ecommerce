@props(['title' => null])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php($settings = app(\App\Services\SettingsService::class))

        <title>
            @if (filled($title ?? null))
                {{ $title }} â€” {{ $settings->get('store_name') }} Admin
            @else
                {{ $settings->get('store_name') }} Admin
            @endif
        </title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="admin-body min-h-screen bg-slate-50 text-slate-900 antialiased">
        <a
            href="#admin-main"
            class="sr-only rounded-md bg-slate-900 px-4 py-2 text-white focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50"
        >
            Skip to content
        </a>

        <div class="relative min-h-screen lg:flex">
            {{-- Without JavaScript the drawer cannot be opened, so the navigation is rendered inline instead. --}}
            <noscript>
                <div class="bg-slate-950 px-4 py-4 lg:hidden">
                    @include('components.partials.admin-sidebar')
                </div>
            </noscript>

            <!-- Mobile sidebar overlay and drawer -->
            <div
                id="admin-mobile-sidebar"
                data-admin-sidebar
                hidden
                class="fixed inset-0 z-40 lg:hidden"
                role="dialog"
                aria-modal="true"
                aria-labelledby="admin-mobile-sidebar-title"
            >
                <div data-admin-sidebar-backdrop class="absolute inset-0 bg-slate-900/40 transition-opacity"></div>
                <div class="relative flex h-full w-full max-w-xs flex-col bg-slate-950 text-white shadow-xl">
                    <div class="flex h-16 items-center justify-between border-b border-white/5 px-5">
                        <div class="flex items-center gap-3">
                            <x-store-brand variant="dark" rounded="rounded-lg" :show-name="false" :max-height="40" />
                            <div>
                                @unless ($settings->usesLogo())
                                    <p class="text-sm font-semibold leading-tight">{{ $settings->get('store_name') }}</p>
                                @endunless
                                <p id="admin-mobile-sidebar-title" class="text-xs text-slate-400">Admin panel</p>
                            </div>
                        </div>
                        <button
                            type="button"
                            data-admin-sidebar-close
                            class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-white/5 hover:text-white"
                            aria-label="Close navigation"
                        >
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto px-3 py-4">
                        @include('components.partials.admin-sidebar')
                    </div>
                    <div class="border-t border-white/5 px-4 py-4">
                        <div class="flex items-center gap-3 px-2">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-sm font-medium">
                                {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-medium">{{ auth()->user()?->name }}</p>
                                <p class="truncate text-xs text-slate-400">{{ auth()->user()?->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Desktop sidebar -->
            <aside class="fixed inset-y-0 left-0 z-30 hidden w-72 flex-col bg-slate-950 text-white lg:flex">
                <div class="flex h-16 items-center gap-3 border-b border-white/5 px-6">
                    <x-store-brand variant="dark" rounded="rounded-lg" :show-name="false" :max-height="40" />
                    <div class="min-w-0">
                        @unless ($settings->usesLogo())
                            <p class="truncate text-sm font-semibold leading-tight">{{ $settings->get('store_name') }}</p>
                        @endunless
                        <p class="text-xs text-slate-400">Admin panel</p>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto px-3 py-4">
                    @include('components.partials.admin-sidebar')
                </div>
                <div class="border-t border-white/5 px-4 py-4">
                    <div class="flex items-center gap-3 rounded-lg px-2 py-1.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-white/10 text-sm font-medium">
                            {{ strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">{{ auth()->user()?->name }}</p>
                            <p class="truncate text-xs text-slate-400">{{ auth()->user()?->email }}</p>
                        </div>
                    </div>
                </div>
            </aside>

            <div class="flex min-h-screen w-full flex-col lg:pl-72">
                <header class="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white/95 px-4 backdrop-blur sm:px-6 lg:px-8">
                    <button
                        type="button"
                        data-admin-sidebar-toggle
                        aria-expanded="false"
                        aria-controls="admin-mobile-sidebar"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 lg:hidden"
                        aria-label="Open navigation"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" stroke-linecap="round" />
                        </svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-slate-900">
                            {{ $title ?? 'Admin' }}
                        </p>
                    </div>

                    <nav aria-label="Admin shortcuts" class="flex items-center gap-1 sm:gap-2">
                        <a
                            href="{{ route('home') }}"
                            class="inline-flex h-9 items-center gap-2 rounded-lg px-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 sm:px-3"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <path d="M3 10.5 12 3l9 7.5V20a1 1 0 0 1-1 1h-5.25v-6.75h-5.5V21H4a1 1 0 0 1-1-1v-9.5Z" stroke-linejoin="round" />
                            </svg>
                            <span class="hidden sm:inline">View store</span>
                        </a>
                        <a
                            href="{{ route('account.show') }}"
                            class="inline-flex h-9 items-center gap-2 rounded-lg px-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 sm:px-3"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                <circle cx="12" cy="8.25" r="3.75" />
                                <path d="M4.75 19.75c.9-3.3 3.6-5 7.25-5s6.35 1.7 7.25 5" stroke-linecap="round" />
                            </svg>
                            <span class="hidden sm:inline">Account</span>
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-2.5 text-sm text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-900 sm:px-3">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                    <path d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="hidden sm:inline">Log out</span>
                            </button>
                        </form>
                    </nav>
                </header>

                <main id="admin-main" class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-6 flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                            <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <p>{{ session('status') }}</p>
                        </div>
                    @endif

                    @if (isset($errors) && $errors->any())
                        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800" role="alert">
                            <div class="flex items-start gap-3">
                                <span class="mt-0.5 inline-flex h-5 w-5 items-center justify-center rounded-full bg-rose-100">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <ul class="space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    {{ $slot }}
                </main>
            </div>
        </div>

        <dialog
            data-confirm-dialog
            class="w-full max-w-[calc(100vw_-_2rem)] max-w-md rounded-xl border border-slate-200 bg-white p-0 shadow-xl backdrop:bg-slate-900/40"
            aria-labelledby="admin-confirm-title"
        >
            <div class="p-6">
                <h2 id="admin-confirm-title" class="text-base font-semibold text-slate-900">Please confirm</h2>
                <p data-confirm-message class="mt-2 text-sm text-slate-600"></p>

                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" data-confirm-cancel class="admin-btn admin-btn-secondary">Cancel</button>
                    <button type="button" data-confirm-accept class="admin-btn admin-btn-danger">Confirm</button>
                </div>
            </div>
        </dialog>
    </body>
</html>
