<x-layouts.shop title="Forgot your password?">
    <div class="shell flex min-h-[70vh] items-center justify-center py-14">
        <div class="w-full max-w-md">
            <p class="eyebrow text-center">Account recovery</p>
            <h1 class="mt-3 text-center font-display text-4xl">Forgot your password?</h1>
            <p class="mt-4 text-center text-sm text-charcoal/65">
                Enter your email address and we will send you a link to reset your password.
            </p>

            <div class="surface mt-8 p-7 sm:p-9">
                <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
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

                    <button type="submit" class="btn btn-primary w-full">Email password reset link</button>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-charcoal/60">
                <a href="{{ route('login') }}" class="link-quiet">&larr; Back to log in</a>
            </p>
        </div>
    </div>
</x-layouts.shop>
