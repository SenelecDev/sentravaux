@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="mt-6 mb-8">
        {{-- Navigation row: Previous - Page numbers - Next --}}
        <div class="flex items-center justify-between">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Précédent
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center px-4 py-2 text-sm font-medium text-senelec-purple bg-white border border-gray-200 rounded-lg hover:bg-senelec-purple hover:text-white hover:border-senelec-purple transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Précédent
                </a>
            @endif

            {{-- Page numbers (center) --}}
            <div class="hidden sm:flex items-center gap-2">
                @php
                    $currentPage = $paginator->currentPage();
                    $lastPage = $paginator->lastPage();
                    $start = max(1, $currentPage - 3);
                    $end = min($lastPage, $currentPage + 3);
                @endphp
                
                @if($start > 1)
                    <a href="{{ $paginator->url(1) }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-senelec-purple/10 hover:text-senelec-purple hover:border-senelec-purple/30 transition-all duration-200">1</a>
                    @if($start > 2)
                        <span class="inline-flex items-center justify-center w-10 h-10 text-gray-400 text-sm">...</span>
                    @endif
                @endif
                
                @for($page = $start; $page <= $end; $page++)
                    @if($page == $currentPage)
                        <span aria-current="page" class="inline-flex items-center justify-center w-10 h-10 text-sm font-semibold text-white bg-senelec-purple rounded-lg shadow-sm">
                            {{ $page }}
                        </span>
                    @else
                        <a href="{{ $paginator->url($page) }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-senelec-purple/10 hover:text-senelec-purple hover:border-senelec-purple/30 transition-all duration-200">
                            {{ $page }}
                        </a>
                    @endif
                @endfor
                
                @if($end < $lastPage)
                    @if($end < $lastPage - 1)
                        <span class="inline-flex items-center justify-center w-10 h-10 text-gray-400 text-sm">...</span>
                    @endif
                    <a href="{{ $paginator->url($lastPage) }}" class="inline-flex items-center justify-center w-10 h-10 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-senelec-purple/10 hover:text-senelec-purple hover:border-senelec-purple/30 transition-all duration-200">{{ $lastPage }}</a>
                @endif
            </div>

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center px-4 py-2 text-sm font-medium text-senelec-purple bg-white border border-gray-200 rounded-lg hover:bg-senelec-purple hover:text-white hover:border-senelec-purple transition-all duration-200">
                    Suivant
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            @else
                <span class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-400 bg-gray-100 border border-gray-200 rounded-lg cursor-not-allowed">
                    Suivant
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </span>
            @endif
        </div>

        {{-- Info text --}}
        <div class="text-sm text-gray-600 text-center mt-4">
            Affichage de <span class="font-semibold text-senelec-purple">{{ $paginator->firstItem() }}</span>
            à <span class="font-semibold text-senelec-purple">{{ $paginator->lastItem() }}</span>
            sur <span class="font-semibold text-senelec-purple">{{ $paginator->total() }}</span> résultats
        </div>
    </nav>
@endif
