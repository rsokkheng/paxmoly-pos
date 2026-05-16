@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span class="page-btn" style="opacity:0.4;cursor:not-allowed;">&laquo;</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn" rel="prev">&laquo;</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span class="page-btn" style="opacity:0.4;">{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="page-btn active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn" rel="next">&raquo;</a>
    @else
        <span class="page-btn" style="opacity:0.4;cursor:not-allowed;">&raquo;</span>
    @endif
@endif
