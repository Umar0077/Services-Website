@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-14 flex items-center justify-between gap-4">
        @if ($paginator->onFirstPage())
            <span class="btn btn-outline btn-sm pointer-events-none opacity-50" aria-disabled="true"><x-glyph name="chevron-left" /> Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-outline btn-sm"><x-glyph name="chevron-left" /> Previous</a>
        @endif

        <ul class="hidden items-center gap-1 sm:flex">
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="px-2 text-muted">{{ $element }}</li>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <li>
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page" class="inline-flex size-11 items-center justify-center rounded-lg bg-teal-900 font-semibold text-white">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="inline-flex size-11 items-center justify-center rounded-lg font-medium text-ink hover:bg-white" aria-label="Page {{ $page }}">{{ $page }}</a>
                            @endif
                        </li>
                    @endforeach
                @endif
            @endforeach
        </ul>
        <p class="text-sm text-muted sm:hidden">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</p>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-outline btn-sm">Next <x-glyph name="chevron-right" /></a>
        @else
            <span class="btn btn-outline btn-sm pointer-events-none opacity-50" aria-disabled="true">Next <x-glyph name="chevron-right" /></span>
        @endif
    </nav>
@endif
