<x-layouts.shop title="Account">
    <div class="shell pt-10 lg:pt-14">
        <p class="eyebrow">Your account</p>
        <h1 class="mt-3 font-display text-4xl sm:text-5xl">My account</h1>
        <div class="mt-3 flex flex-wrap items-center justify-between gap-4">
            <p class="text-sm text-charcoal/55">Member since {{ $user->created_at?->format('M j, Y') }}</p>

            @if ($user->isAdmin())
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M4 5.75h16M4 12h16M4 18.25h16" stroke-linecap="round" />
                        <circle cx="8" cy="5.75" r="1.5" />
                        <circle cx="16" cy="12" r="1.5" />
                        <circle cx="10" cy="18.25" r="1.5" />
                    </svg>
                    Admin panel
                </a>
            @endif
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-sm border border-olive/30 bg-olive/10 px-4 py-3 text-sm text-charcoal" role="status">
                {{ session('status') }}
            </div>
        @endif

        <div class="mt-10 grid gap-8 lg:grid-cols-2 lg:gap-12">
            <section class="surface p-7 sm:p-9">
                <h2 class="font-display text-2xl">Profile details</h2>

                <form method="POST" action="{{ route('account.update') }}" class="mt-6 flex flex-col gap-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="name" class="field-label">Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                            class="field"
                        >
                        @error('name')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="email" class="field-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                            class="field"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Update profile</button>
                </form>

                <hr class="my-8 border-sand">

                <h2 class="font-display text-2xl">Change password</h2>

                <form method="POST" action="{{ route('account.password.update') }}" class="mt-6 flex flex-col gap-5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label for="current_password" class="field-label">Current password</label>
                        <input
                            id="current_password"
                            type="password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="field"
                        >
                        @error('current_password')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="field-label">New password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="field"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label">Confirm new password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="field"
                        >
                    </div>

                    <button type="submit" class="btn btn-outline w-full sm:w-auto">Change password</button>
                </form>
            </section>

            <section class="surface h-fit p-7 sm:p-9">
                <h2 class="font-display text-2xl">Order history</h2>

                @if ($orders->isEmpty())
                    <p class="mt-4 text-sm text-charcoal/60">You have not placed any orders yet.</p>
                @else
                    <div class="mt-6 overflow-x-auto">
                        <table class="w-full min-w-[560px] text-left text-sm">
                            <caption class="sr-only">Order history</caption>
                            <thead class="border-b border-sand text-[0.625rem] uppercase tracking-[0.16em] text-charcoal/50">
                                <tr>
                                    <th scope="col" class="py-2 pr-4 font-medium">Order</th>
                                    <th scope="col" class="py-2 pr-4 font-medium">Date</th>
                                    <th scope="col" class="py-2 pr-4 font-medium">Status</th>
                                    <th scope="col" class="py-2 pr-4 font-medium">Total</th>
                                    <th scope="col" class="py-2 font-medium"><span class="sr-only">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($orders as $order)
                                    <tr class="border-b border-sand/60">
                                        <th scope="row" class="py-3 pr-4 font-medium text-charcoal">#{{ $order->id }}</th>
                                        <td class="py-3 pr-4 text-charcoal/70">{{ $order->created_at?->format('M j, Y') }}</td>
                                        <td class="py-3 pr-4 capitalize text-charcoal/70">{{ $order->status->value }}</td>
                                        <td class="py-3 pr-4 text-charcoal">{{ \App\Support\Money::format($order->total) }}</td>
                                        <td class="py-3">
                                            <a href="{{ route('account.orders.show', $order) }}" class="link-quiet">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6">
                        {{ $orders->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
</x-layouts.shop>
