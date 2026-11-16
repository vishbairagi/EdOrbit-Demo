@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between gap-3 px-4 py-3 bg-white border-t border-[#EAE3DC] text-xs">
        @if ($paginator->onFirstPage())
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed">
                &laquo; {!! __('pagination.previous') !!}
            </span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-[#DED5CB] text-xs font-semibold text-heading bg-white hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                &laquo; {!! __('pagination.previous') !!}
            </a>
        @endif

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-[#DED5CB] text-xs font-semibold text-heading bg-white hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                {!! __('pagination.next') !!} &raquo;
            </a>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed">
                {!! __('pagination.next') !!} &raquo;
            </span>
        @endif
    </nav>
@endif
