@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ $ariaLabel ?? 'Pagination Navigation' }}"
        class="mt-4 flex flex-col items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white px-5 py-4 shadow-sm sm:flex-row"
    >
        <p class="text-sm text-slate-500">
            Showing
            <span class="font-medium text-slate-900">{{ $paginator->firstItem() }}</span>
            to
            <span class="font-medium text-slate-900">{{ $paginator->lastItem() }}</span>
            of
            <span class="font-medium text-slate-900">{{ $paginator->total() }}</span>
        </p>

        <div class="flex items-center gap-1">
            @if ($paginator->onFirstPage())
                <span class="admin-btn admin-btn-secondary admin-btn-sm pointer-events-none opacity-50" aria-disabled="true">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="admin-btn admin-btn-secondary admin-btn-sm">Previous</a>
            @endif

            <div class="hidden items-center gap-1 sm:flex">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 text-sm text-slate-400">{{ $element }}</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="admin-btn admin-btn-primary admin-btn-sm">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="admin-btn admin-btn-ghost admin-btn-sm">{{ $page }}</a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="admin-btn admin-btn-secondary admin-btn-sm">Next</a>
            @else
                <span class="admin-btn admin-btn-secondary admin-btn-sm pointer-events-none opacity-50" aria-disabled="true">Next</span>
            @endif
        </div>
    </nav>
@endif
