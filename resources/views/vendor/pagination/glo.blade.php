@if ($paginator->hasPages())
    <nav class="glo-pagination" role="navigation" aria-label="Navigasi Halaman">
        <div class="glo-page-info">
            Menampilkan <strong>{{ $paginator->firstItem() }}</strong>–<strong>{{ $paginator->lastItem() }}</strong> dari <strong>{{ $paginator->total() }}</strong> data
        </div>

        <div class="glo-page-links">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="glo-page-btn disabled" aria-disabled="true" aria-label="Sebelumnya">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </span>
            @else
                <a class="glo-page-btn" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Sebelumnya">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </a>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="glo-page-ellipsis" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="glo-page-btn active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="glo-page-btn" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <a class="glo-page-btn" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Berikutnya">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </a>
            @else
                <span class="glo-page-btn disabled" aria-disabled="true" aria-label="Berikutnya">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
