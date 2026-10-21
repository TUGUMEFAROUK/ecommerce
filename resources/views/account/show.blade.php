<x-layouts.app :title="'Account'">
    <div class="flex flex-col gap-8">
        <div class="rounded border border-gray-200 bg-white p-8">
            <h1 class="mb-6 text-xl font-semibold">My account</h1>

            <p class="mb-1 text-sm text-gray-600">Member since {{ $user->created_at?->format('M j, Y') }}</p>

            <form method="POST" action="{{ route('account.update') }}" class="mt-6 flex max-w-md flex-col gap-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="name" class="mb-1 block text-sm font-medium">Name</label>
                    <input
                        id="name"
                        type="text"
                        name="name"
                        value="{{ old('name', $user->name) }}"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    >
                </div>

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', $user->email) }}"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    >
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-700">
                        Update profile
                    </button>
                </div>
            </form>

            <hr class="my-8 border-gray-200">

            <h2 class="mb-4 text-lg font-semibold">Change password</h2>

            <form method="POST" action="{{ route('account.password.update') }}" class="flex max-w-md flex-col gap-4">
                @csrf
                @method('PATCH')

                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium">Current password</label>
                    <input
                        id="current_password"
                        type="password"
                        name="current_password"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    >
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium">New password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    >
                </div>

                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm new password</label>
                    <input
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        class="w-full rounded border border-gray-300 px-3 py-2"
                    >
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-700">
                        Change password
                    </button>
                </div>
            </form>
        </div>

        <div class="rounded border border-gray-200 bg-white p-8">
            <h2 class="mb-4 text-lg font-semibold">Order history</h2>

            @if ($orders->isEmpty())
                <p class="text-sm text-gray-600">You have not placed any orders yet.</p>
            @else
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 text-gray-600">
                        <tr>
                            <th class="py-2 pr-4 font-medium">Order</th>
                            <th class="py-2 pr-4 font-medium">Date</th>
                            <th class="py-2 pr-4 font-medium">Status</th>
                            <th class="py-2 pr-4 font-medium">Total</th>
                            <th class="py-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 pr-4">#{{ $order->id }}</td>
                                <td class="py-2 pr-4">{{ $order->created_at?->format('M j, Y') }}</td>
                                <td class="py-2 pr-4 capitalize">{{ $order->status->value }}</td>
                                <td class="py-2 pr-4">{{ $order->currency }} {{ number_format((float) $order->total, 2) }}</td>
                                <td class="py-2">
                                    <a href="{{ route('account.orders.show', $order) }}" class="text-gray-900 underline">
                                        View
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</x-layouts.app>
