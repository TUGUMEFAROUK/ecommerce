<x-layouts.shop title="Log in">
    <div class="shell flex min-h-[70vh] items-center justify-center py-14">
        <div class="w-full max-w-md">
            <p class="eyebrow text-center">Welcome back</p>
            <h1 class="mt-3 text-center font-display text-4xl">Log in</h1>

            <div class="surface mt-8 p-7 sm:p-9">
                <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-5">
                    @csrf

                    <div>
                        <label for="email" class="field-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="field"
                        >
                        @error('email')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="field-label">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="field"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <label class="flex items-center gap-2 text-sm text-charcoal/70">
                        <input id="remember" type="checkbox" name="remember" value="1" class="h-4 w-4 rounded-sm border-sand text-charcoal focus:ring-olive">
                        Remember me
                    </label>

                    <div class="flex flex-col items-center gap-4 pt-1">
                        <button type="submit" class="btn btn-primary w-full">Log in</button>

                        <a href="{{ route('password.request') }}" class="link-quiet text-sm">Forgot your password?</a>
                    </div>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-charcoal/60">
                New to the store?
                <a href="{{ route('register') }}" class="link-quiet">Create an account</a>
            </p>
        </div>
    </div>
</x-layouts.shop>
