@if ($paginator->hasPages())
    <nav class="pagination" role="navigation" aria-label="Pagination">
        <div>
            Showing <b>{{ $paginator->firstItem() }}</b>–<b>{{ $paginator->lastItem() }}</b>
            of <b>{{ $paginator->total() }}</b> results
        </div>

        <div class="pagination-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="page-link disabled" aria-disabled="true" aria-label="Previous">
                    <x-icon name="chevron-left" class="icon-sm" />
                </span>
            @else
                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">
                    <x-icon name="chevron-left" class="icon-sm" />
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="page-link disabled" aria-disabled="true">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="page-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">
                    <x-icon name="chevron-right" class="icon-sm" />
                </a>
            @else
                <span class="page-link disabled" aria-disabled="true" aria-label="Next">
                    <x-icon name="chevron-right" class="icon-sm" />
                </span>
            @endif
        </div>
    </nav>
@endif