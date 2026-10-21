<x-layouts.app title="Log in">
    <div class="mx-auto max-w-md rounded border border-gray-200 bg-white p-8">
        <h1 class="mb-6 text-xl font-semibold">Log in</h1>

        <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
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

            <div class="flex items-center gap-2">
                <input id="remember" type="checkbox" name="remember" value="1" class="rounded border-gray-300">
                <label for="remember" class="text-sm">Remember me</label>
            </div>

            <div class="flex items-center justify-between text-sm">
                <a href="{{ route('password.request') }}" class="text-gray-600 hover:text-gray-900">
                    Forgot your password?
                </a>

                <button type="submit" class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-700">
                    Log in
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
