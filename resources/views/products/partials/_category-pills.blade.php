@php
    $categories = $categories ?? $navCategories ?? collect();
    $activeCategorySlug = request()->routeIs('products.category') ? request()->route('slug') : null;
    $isAllActive = request()->routeIs('products.index') && ! request()->filled('category_id');
@endphp

@if($categories->isNotEmpty())
    <style>
        [data-category-pills] {
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
            scrollbar-width: none;
        }
        [data-category-pills]::-webkit-scrollbar {
            display: none;
        }
        [data-category-scroll-wrap].fade-start::before,
        [data-category-scroll-wrap].fade-end::after {
            opacity: 1;
        }
        [data-category-scroll-wrap]::before,
        [data-category-scroll-wrap]::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 1.5rem;
            pointer-events: none;
            z-index: 2;
            opacity: 0;
            transition: opacity 0.2s ease;
        }
        [data-category-scroll-wrap]::before {
            left: 0;
            background: linear-gradient(to right, #fff7ef 30%, transparent);
        }
        [data-category-scroll-wrap]::after {
            right: 0;
            background: linear-gradient(to left, #fff7ef 30%, transparent);
        }
        [data-category-scroll-start][hidden],
        [data-category-scroll-end][hidden] {
            display: none !important;
        }
        [data-category-pills-bar].is-centered [data-category-pills] {
            justify-content: center;
            overflow-x: visible;
        }
    </style>

    <div class="border-t border-amber-100/80 bg-gradient-to-b from-amber-50/90 to-amber-50/50" data-testid="category-pills-bar">
        <div class="mx-auto flex max-w-7xl items-center gap-1 px-2 py-2 sm:gap-2 sm:px-4 sm:py-2.5">
            <button
                type="button"
                class="catalog-category-nav-prev inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-200/90 bg-white text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-1"
                data-category-scroll-start
                aria-label="{{ __('Scroll to previous categories') }}"
                hidden
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
                </svg>
            </button>

            <div class="relative min-w-0 flex-1" data-category-scroll-wrap>
                <div
                    class="flex gap-2 overflow-x-auto overscroll-x-contain px-1 py-0.5 sm:gap-2.5 sm:px-2"
                    data-category-pills
                    role="navigation"
                    aria-label="{{ __('Product categories') }}"
                >
                    <a
                        href="{{ route('products.index', request()->only('sort')) }}"
                        class="inline-flex shrink-0 snap-start items-center justify-center whitespace-nowrap rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-1 {{ $isAllActive ? 'border-transparent bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md' : 'border-stone-200/90 bg-white text-stone-700 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800' }}"
                        @if($isAllActive) aria-current="page" @endif
                        data-category-pill="all"
                    >
                        {{ __('All Categories') }}
                    </a>
                    @foreach($categories as $category)
                        @php $isActive = $activeCategorySlug === $category->slug; @endphp
                        <a
                            href="{{ route('products.category', $category->slug) }}"
                            class="inline-flex shrink-0 snap-start items-center justify-center whitespace-nowrap rounded-full border px-4 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-1 {{ $isActive ? 'border-transparent bg-gradient-to-r from-amber-500 to-orange-500 text-white shadow-md' : 'border-stone-200/90 bg-white text-stone-700 hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800' }}"
                            @if($isActive) aria-current="page" @endif
                            data-category-pill="{{ $category->slug }}"
                        >
                            {{ $category->name_en }}
                        </a>
                    @endforeach
                </div>
            </div>

            <button
                type="button"
                class="catalog-category-nav-next inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-amber-200/90 bg-white text-amber-700 shadow-sm transition hover:border-amber-300 hover:bg-amber-50 hover:text-amber-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-1"
                data-category-scroll-end
                aria-label="{{ __('More categories') }}"
                hidden
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>
    </div>

    @once
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.querySelectorAll('[data-testid="category-pills-bar"]').forEach(function (bar) {
                    var scroll = bar.querySelector('[data-category-pills]');
                    var wrap = bar.querySelector('[data-category-scroll-wrap]');
                    var hintStart = bar.querySelector('[data-category-scroll-start]');
                    var hintEnd = bar.querySelector('[data-category-scroll-end]');
                    if (!scroll || !wrap) return;

                    function updateScrollHints() {
                        var maxScroll = Math.max(0, scroll.scrollWidth - scroll.clientWidth);
                        var hasOverflow = maxScroll > 8;
                        var atStart = scroll.scrollLeft <= 8;
                        var atEnd = scroll.scrollLeft >= maxScroll - 8;

                        bar.classList.toggle('is-centered', !hasOverflow);

                        if (hintStart) hintStart.hidden = !hasOverflow || atStart;
                        if (hintEnd) hintEnd.hidden = !hasOverflow || atEnd;

                        wrap.classList.toggle('fade-start', hasOverflow && !atStart);
                        wrap.classList.toggle('fade-end', hasOverflow && !atEnd);
                    }

                    function scrollByDirection(direction) {
                        scroll.scrollBy({ left: direction * 200, behavior: 'smooth' });
                    }

                    if (hintStart) {
                        hintStart.addEventListener('click', function () {
                            scrollByDirection(-1);
                        });
                    }

                    if (hintEnd) {
                        hintEnd.addEventListener('click', function () {
                            scrollByDirection(1);
                        });
                    }

                    scroll.addEventListener('scroll', updateScrollHints, { passive: true });
                    window.addEventListener('resize', updateScrollHints);

                    if (typeof ResizeObserver !== 'undefined') {
                        var resizeObserver = new ResizeObserver(function () {
                            updateScrollHints();
                        });
                        resizeObserver.observe(scroll);
                    }

                    var active = scroll.querySelector('[aria-current="page"]');
                    if (active) {
                        active.scrollIntoView({ inline: 'center', block: 'nearest' });
                        requestAnimationFrame(updateScrollHints);
                    }

                    updateScrollHints();
                    requestAnimationFrame(updateScrollHints);
                });
            });
        </script>
    @endonce
@endif
