@if ($paginator->hasPages())
    <nav class="paginasi" aria-label="Halaman">
        @if ($paginator->onFirstPage())
            <span class="mati" aria-hidden="true"><i class="bi bi-chevron-left"></i></span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya"><i class="bi bi-chevron-left"></i></a>
        @endif
        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="mati">{{ $element }}</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="aktif" aria-current="page">{{ $page }}</span>
                    @else
                        <a href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya"><i class="bi bi-chevron-right"></i></a>
        @else
            <span class="mati" aria-hidden="true"><i class="bi bi-chevron-right"></i></span>
        @endif
    </nav>
@endif
