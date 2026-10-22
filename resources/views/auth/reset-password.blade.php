<x-layouts.shop title="Reset password">
    <div class="shell flex min-h-[70vh] items-center justify-center py-14">
        <div class="w-full max-w-md">
            <p class="eyebrow text-center">Account recovery</p>
            <h1 class="mt-3 text-center font-display text-4xl">Reset password</h1>

            <div class="surface mt-8 p-7 sm:p-9">
                <form method="POST" action="{{ route('password.update') }}" class="flex flex-col gap-5">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div>
                        <label for="email" class="field-label">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $email) }}"
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

                    <button type="submit" class="btn btn-primary w-full">Reset password</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.shop>
