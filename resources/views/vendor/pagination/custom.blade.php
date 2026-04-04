@if ($paginator->hasPages())
<nav class="pagination" aria-label="Pagination">
    {{-- Previous --}}
    @if ($paginator->onFirstPage())
    <span class="pagination__btn pagination__btn--disabled">‹</span>
    @else
    <a class="pagination__btn" href="{{ $paginator->previousPageUrl() }}">‹</a>
    @endif

    {{-- Page numbers --}}
    @foreach ($elements as $element)
    @if (is_string($element))
    <span class="pagination__btn pagination__btn--dots">…</span>
    @endif

    @if (is_array($element))
    @foreach ($element as $page => $url)
    @if ($page == $paginator->currentPage())
    <span class="pagination__btn pagination__btn--active">{{ $page }}</span>
    @else
    <a class="pagination__btn" href="{{ $url }}">{{ $page }}</a>
    @endif
    @endforeach
    @endif
    @endforeach

    {{-- Next --}}
    @if ($paginator->hasMorePages())
    <a class="pagination__btn" href="{{ $paginator->nextPageUrl() }}">›</a>
    @else
    <span class="pagination__btn pagination__btn--disabled">›</span>
    @endif

    <span class="pagination__info">
        {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </span>
</nav>
@endif