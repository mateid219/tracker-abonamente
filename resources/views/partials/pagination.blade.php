{{-- Pagination without any CSS framework. Usage: $items->links('partials.pagination') --}}
@if ($paginator->hasPages())
    <nav class="pagination" aria-label="Paginare">
        @if ($paginator->onFirstPage())
            <span class="pagination-link is-disabled" aria-disabled="true">&larr; Înapoi</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="pagination-link" rel="prev">&larr; Înapoi</a>
        @endif

        <span class="pagination-info">
            Pagina {{ $paginator->currentPage() }} din {{ $paginator->lastPage() }}
            <span class="muted">({{ $paginator->total() }} conturi)</span>
        </span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="pagination-link" rel="next">Înainte &rarr;</a>
        @else
            <span class="pagination-link is-disabled" aria-disabled="true">Înainte &rarr;</span>
        @endif
    </nav>
@endif
