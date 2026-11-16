@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 bg-white border-t border-[#EAE3DC] text-xs">
        {{-- Mobile View --}}
        <div class="flex items-center justify-between w-full sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed">
                    &laquo; Previous
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-[#DED5CB] text-xs font-semibold text-heading bg-white hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                    &laquo; Previous
                </a>
            @endif

            <span class="text-xs text-body font-medium">
                Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}
            </span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-3 py-1.5 rounded-lg border border-[#DED5CB] text-xs font-semibold text-heading bg-white hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                    Next &raquo;
                </a>
            @else
                <span class="inline-flex items-center px-3 py-1.5 rounded-lg border border-gray-200 text-xs font-medium text-gray-400 bg-gray-50 cursor-not-allowed">
                    Next &raquo;
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-xs text-body">
                    Showing
                    @if ($paginator->firstItem())
                        <span class="font-bold text-heading">{{ $paginator->firstItem() }}</span>
                        to
                        <span class="font-bold text-heading">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    of
                    <span class="font-bold text-heading">{{ $paginator->total() }}</span>
                    entries
                </p>
            </div>

            <div class="inline-flex items-center gap-1">
                {{-- Previous Link --}}
                @if ($paginator->onFirstPage())
                    <span aria-disabled="true" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-300 bg-gray-50 text-xs cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </span>
                @else
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-[#DED5CB] bg-white text-heading text-xs font-medium hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </a>
                @endif

                {{-- Page Elements --}}
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="inline-flex items-center px-2 py-1.5 text-xs text-body font-bold">...</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg text-xs font-bold text-white shadow-xs" style="background:#B47A42; border:1px solid #B47A42;">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="inline-flex items-center justify-center min-w-[32px] h-8 px-2 rounded-lg border border-[#DED5CB] bg-white text-heading text-xs font-semibold hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach

                {{-- Next Link --}}
                @if ($paginator->hasMorePages())
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-[#DED5CB] bg-white text-heading text-xs font-medium hover:bg-[#F5ECE0] hover:text-[#B47A42] transition shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                @else
                    <span aria-disabled="true" class="inline-flex items-center px-2.5 py-1.5 rounded-lg border border-gray-200 text-gray-300 bg-gray-50 text-xs cursor-not-allowed">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </div>
    </nav>
@endif
