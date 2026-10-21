<x-layouts.app title="Forgot your password?">
    <div class="mx-auto max-w-md rounded border border-gray-200 bg-white p-8">
        <h1 class="mb-2 text-xl font-semibold">Forgot your password?</h1>
        <p class="mb-6 text-sm text-gray-600">
            Enter your email address and we will send you a link to reset your password.
        </p>

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    class="w-full rounded border border-gray-300 px-3 py-2"
                >
            </div>

            <div class="flex justify-end">
                <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-700">
                    Email password reset link
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
