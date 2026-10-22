@props(['product', 'images' => null])

@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('public');
    $images ??= $product->images;

    $slides = [];

    foreach ($images as $image) {
        if (! $disk->exists($image->path)) {
            continue;
        }

        $slides[] = [
            'url' => $disk->url($image->path),
            'alt' => $slides === [] ? $product->name : $product->name.' — view '.(count($slides) + 1),
            'placeholder' => false,
        ];
    }

    if ($slides === []) {
        $slides[] = [
            'url' => asset('images/product-placeholder.svg'),
            'alt' => $product->name.' (no image available)',
            'placeholder' => true,
        ];
    }

    $count = count($slides);
    $first = $slides[0];
@endphp

<section {{ $attributes->merge(['class' => 'flex flex-col gap-4']) }} aria-label="Product images" data-gallery>
    <div class="relative overflow-hidden rounded-sm border border-sand bg-white">
        <a
            href="{{ $first['url'] }}"
            data-gallery-open
            class="block aspect-[4/5] w-full cursor-zoom-in"
            aria-label="Enlarge image 1 of {{ $count }}"
        >
            <img
                data-gallery-image
                src="{{ $first['url'] }}"
                alt="{{ $first['alt'] }}"
                width="1000"
                height="1250"
                loading="eager"
                fetchpriority="high"
                decoding="async"
                class="h-full w-full {{ $first['placeholder'] ? 'object-contain p-10 opacity-80' : 'object-cover' }}"
            >
        </a>

        <span class="pointer-events-none absolute bottom-4 right-4 flex items-center gap-1.5 rounded-sm bg-white/90 px-2.5 py-1.5 text-[0.625rem] font-medium uppercase tracking-[0.15em] text-charcoal backdrop-blur-sm">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="11" cy="11" r="6.25" />
                <path d="M11 8.5v5M8.5 11h5M15.5 15.5 20 20" stroke-linecap="round" />
            </svg>
            Zoom
        </span>
    </div>

    <p data-gallery-status class="sr-only" aria-live="polite">Image 1 of {{ $count }}</p>

    @if ($count > 1)
        <ul class="grid grid-cols-4 gap-3" aria-label="Product image thumbnails">
            @foreach ($slides as $slide)
                <li>
                    <button
                        type="button"
                        data-gallery-thumb
                        data-src="{{ $slide['url'] }}"
                        data-alt="{{ $slide['alt'] }}"
                        data-placeholder="{{ $slide['placeholder'] ? 'true' : 'false' }}"
                        aria-label="Show image {{ $loop->iteration }} of {{ $count }}"
                        aria-current="{{ $loop->first ? 'true' : 'false' }}"
                        @class([
                            'block aspect-square w-full overflow-hidden rounded-sm border bg-white transition-colors duration-200',
                            'border-charcoal' => $loop->first,
                            'border-sand hover:border-charcoal/40' => ! $loop->first,
                        ])
                    >
                        <img
                            src="{{ $slide['url'] }}"
                            alt=""
                            width="200"
                            height="200"
                            loading="lazy"
                            decoding="async"
                            class="h-full w-full object-cover"
                        >
                    </button>
                </li>
            @endforeach
        </ul>
    @endif

    <dialog
        data-gallery-dialog
        aria-label="Enlarged product image"
        class="m-auto w-[min(96vw,76rem)] max-w-none border-0 bg-transparent p-0 text-charcoal backdrop:bg-charcoal/85"
    >
        <div class="surface p-4 sm:p-6">
            <div class="mb-4 flex items-center justify-between gap-4">
                <p data-gallery-dialog-counter class="eyebrow" aria-hidden="true">Image 1 of {{ $count }}</p>

                <button
                    type="button"
                    data-gallery-close
                    class="inline-flex h-10 w-10 items-center justify-center rounded-sm text-charcoal transition-colors duration-200 hover:bg-ivory"
                >
                    <span class="sr-only">Close enlarged image</span>
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="m6 6 12 12M18 6 6 18" stroke-linecap="round" />
                    </svg>
                </button>
            </div>

            <img
                data-gallery-dialog-image
                alt=""
                class="mx-auto max-h-[75vh] w-auto max-w-full object-contain"
            >

            @if ($count > 1)
                <div class="mt-5 flex items-center justify-between gap-3">
                    <button type="button" data-gallery-prev class="btn btn-sm btn-outline">
                        Previous
                    </button>
                    <button type="button" data-gallery-next class="btn btn-sm btn-outline">
                        Next
                    </button>
                </div>
            @endif
        </div>
    </dialog>
</section>
