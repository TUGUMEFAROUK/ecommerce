<x-layouts.shop :title="$policyTitle">
    <section class="shell pt-10 lg:pt-16">
        <nav aria-label="Breadcrumb" class="mb-8">
            <ol class="flex flex-wrap items-center gap-2 text-[0.6875rem] uppercase tracking-[0.18em] text-charcoal/55">
                <li><a href="{{ route('home') }}" class="transition-colors duration-200 hover:text-olive">Home</a></li>
                <li aria-hidden="true">/</li>
                <li aria-current="page" class="text-charcoal">{{ $policyTitle }}</li>
            </ol>
        </nav>

        <div class="max-w-3xl">
            <p class="eyebrow">Store policy</p>
            <h1 class="mt-3 font-display text-4xl sm:text-5xl">{{ $policyTitle }}</h1>

            <div class="mt-12 space-y-10">
                @foreach ($sections as $section)
                    <div class="border-t border-sand pt-8">
                        <h2 class="font-display text-2xl">{{ $section['heading'] }}</h2>
                        <p class="mt-3 text-base leading-relaxed text-charcoal/70">
                            {{ $section['body'] }}
                        </p>
                    </div>
                @endforeach
            </div>

            <div class="mt-14 border-t border-sand pt-8">
                <a href="{{ route('home') }}" class="btn btn-outline">Back to the store</a>
            </div>
        </div>
    </section>
</x-layouts.shop>
