@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ $ariaLabel ?? 'Pagination Navigation' }}" class="flex flex-col items-center justify-between gap-4 border-t border-sand pt-8 sm:flex-row">
        <p class="text-[0.6875rem] uppercase tracking-[0.16em] text-charcoal/50">
            Showing
            <span class="text-charcoal">{{ $paginator->firstItem() }}</span>
            to
            <span class="text-charcoal">{{ $paginator->lastItem() }}</span>
            of
            <span class="text-charcoal">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="btn btn-ghost btn-sm pointer-events-none opacity-40" aria-disabled="true">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm">Previous</a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-charcoal/40">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="flex h-9 min-w-9 items-center justify-center rounded-sm bg-charcoal px-3 text-[0.6875rem] font-medium uppercase tracking-[0.14em] text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="flex h-9 min-w-9 items-center justify-center rounded-sm px-3 text-[0.6875rem] font-medium uppercase tracking-[0.14em] text-charcoal/70 transition-colors duration-200 hover:bg-sand hover:text-charcoal">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">Next</a>
            @else
                <span class="btn btn-ghost btn-sm pointer-events-none opacity-40" aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif
