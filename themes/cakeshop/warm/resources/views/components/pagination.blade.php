@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex items-center justify-between w-full border-t border-amber-100 pt-8">
        {{-- Mobile View --}}
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center rounded-full border border-amber-200 bg-amber-50/50 px-5 py-2.5 text-sm font-semibold text-stone-400 cursor-not-allowed">
                    {!! __('Previous') !!}
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center rounded-full border border-amber-200 bg-white px-5 py-2.5 text-sm font-bold text-stone-700 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm">
                    {!! __('Previous') !!}
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative ml-3 inline-flex items-center rounded-full border border-amber-200 bg-white px-5 py-2.5 text-sm font-bold text-stone-700 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm">
                    {!! __('Next') !!}
                </a>
            @else
                <span class="relative ml-3 inline-flex items-center rounded-full border border-amber-200 bg-amber-50/50 px-5 py-2.5 text-sm font-semibold text-stone-400 cursor-not-allowed">
                    {!! __('Next') !!}
                </span>
            @endif
        </div>

        {{-- Desktop View --}}
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between w-full">
            <div>
                <p class="text-sm font-medium text-stone-500">
                    {!! __('Showing') !!}
                    @if ($paginator->firstItem())
                        <span class="font-bold text-stone-900 mx-1">{{ $paginator->firstItem() }}</span>
                        {!! __('to') !!}
                        <span class="font-bold text-stone-900 mx-1">{{ $paginator->lastItem() }}</span>
                    @else
                        {{ $paginator->count() }}
                    @endif
                    {!! __('of') !!}
                    <span class="font-bold text-amber-600 mx-1">{{ $paginator->total() }}</span>
                    {!! __('results') !!}
                </p>
            </div>

            <div>
                <nav class="isolate inline-flex items-center gap-1.5" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-300 bg-stone-50 cursor-not-allowed">
                            <span class="sr-only">{{ __('Previous') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 bg-white border border-amber-200 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">{{ __('Previous') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="relative inline-flex items-center justify-center w-10 h-10 text-sm font-semibold text-stone-500 cursor-default">{{ $element }}</span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative z-10 inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-sm font-bold text-white shadow-md focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-600">{{ $page }}</span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full bg-white border border-amber-200 text-sm font-bold text-stone-600 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm focus:z-20 focus:outline-offset-0">{{ $page }}</a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-600 bg-white border border-amber-200 hover:bg-amber-50 hover:text-amber-600 hover:border-amber-300 transition-all shadow-sm focus:z-20 focus:outline-offset-0">
                            <span class="sr-only">{{ __('Next') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </a>
                    @else
                        <span class="relative inline-flex items-center justify-center w-10 h-10 rounded-full text-stone-300 bg-stone-50 cursor-not-allowed">
                            <span class="sr-only">{{ __('Next') }}</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                            </svg>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </nav>
@endif