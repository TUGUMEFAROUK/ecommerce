<x-layouts.shop title="Register">
    <div class="shell flex min-h-[70vh] items-center justify-center py-14">
        <div class="w-full max-w-md">
            <p class="eyebrow text-center">Join us</p>
            <h1 class="mt-3 text-center font-display text-4xl">Create an account</h1>

            <div class="surface mt-8 p-7 sm:p-9">
                <form method="POST" action="{{ route('register') }}" class="flex flex-col gap-5">
                    @csrf

                    <div>
                        <label for="name" class="field-label">Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
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
                            value="{{ old('email') }}"
                            required
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
                            autocomplete="new-password"
                            class="field"
                        >
                        @error('password')
                            <p class="mt-1 text-xs text-clay" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="field-label">Confirm password</label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="field"
                        >
                    </div>

                    <button type="submit" class="btn btn-primary w-full">Create account</button>
                </form>
            </div>

            <p class="mt-8 text-center text-sm text-charcoal/60">
                Already have an account?
                <a href="{{ route('login') }}" class="link-quiet">Log in</a>
            </p>
        </div>
    </div>
</x-layouts.shop>
