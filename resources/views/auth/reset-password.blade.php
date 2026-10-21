<x-layouts.app title="Reset password">
    <div class="mx-auto max-w-md rounded border border-gray-200 bg-white p-8">
        <h1 class="mb-6 text-xl font-semibold">Reset password</h1>

        <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-4">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', $email) }}"
                    required
                    autofocus
                    class="w-full rounded border border-gray-300 px-3 py-2"
                >
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    class="w-full rounded border border-gray-300 px-3 py-2"
                >
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm password</label>
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
                    Reset password
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
