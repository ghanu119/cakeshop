@extends('layouts.app')

@section('title', __('Products') . ' – ' . (settings('site_name') ?: config('app.name')))

@section('content')
@php
    $hasActiveFilters = request()->filled('search')
        || request()->filled('category_id')
        || request()->filled('price_min')
        || request()->filled('price_max')
        || request()->filled('flavor_ids')
        || request()->filled('weight_ids')
        || (request()->filled('sort') && request('sort') !== 'name_asc');
    $filterInputClass = 'w-full min-h-[3rem] rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $filterSelectClass = $filterInputClass;
    $filterLabelClass = 'mb-2.5 block text-sm font-medium text-stone-800';
@endphp

<section class="bg-stone-50 py-5 sm:py-6 lg:py-8" data-testid="products-page">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <header class="mb-5 sm:mb-6">
            <nav class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-stone-500" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="transition-colors hover:text-amber-600">{{ __('Home') }}</a>
                <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                <span class="text-stone-900">{{ __('Products') }}</span>
            </nav>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    <h1 class="font-display text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">
                        {{ __('Our') }} <span class="text-amber-600">{{ __('Products') }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-stone-600">{{ __('Explore our complete collection of handcrafted, delicious cakes') }}</p>
                </div>
                @if($products->total() > 0)
                    <p class="text-sm font-medium text-stone-500">
                        <span class="font-semibold text-amber-600">{{ $products->total() }}</span> {{ __('in catalog') }}
                    </p>
                @endif
            </div>
        </header>

        <div class="catalog-layout">
            {{-- Filters: collapsible on mobile, sticky sidebar on desktop --}}
            <details open class="catalog-filters-panel group rounded-2xl border border-amber-100/90 bg-white shadow-sm lg:sticky lg:top-24 lg:col-start-1 lg:row-start-1 lg:max-h-[calc(100vh-7rem)] lg:overflow-y-auto lg:rounded-2xl lg:border-amber-100/90 lg:bg-white lg:shadow-sm">
                <summary class="catalog-filters-summary flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 text-sm font-semibold text-stone-900 sm:px-5 sm:py-4 lg:hidden [&::-webkit-details-marker]:hidden">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        {{ __('Filters & sort') }}
                    </span>
                    <svg class="catalog-filters-chevron h-5 w-5 shrink-0 text-stone-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="catalog-filters-body border-t border-stone-100 px-5 pb-5 pt-4 sm:px-6 sm:pb-6 sm:pt-5 lg:border-t-0 lg:px-6 lg:pb-6 lg:pt-6">
                    <div class="catalog-filters-head mb-6 hidden border-b border-stone-100 pb-5 lg:block">
                        <h2 class="flex items-center gap-2.5 font-display text-lg font-bold text-stone-900">
                            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                            </span>
                            {{ __('Filter Products') }}
                        </h2>
                        <p class="mt-2 text-sm leading-relaxed text-stone-500">{{ __('Narrow by category, flavor, size, or price') }}</p>
                    </div>
                    <form method="get" action="{{ route('products.index') }}" id="product-filters">
                        @include('products.partials._filters-fields', [
                            'inputClass' => $filterInputClass,
                            'selectClass' => $filterSelectClass,
                            'multiSelectClass' => $filterSelectClass,
                            'priceInputClass' => $filterInputClass,
                            'labelClass' => $filterLabelClass,
                            'fieldsGridClass' => 'product-filters-grid flex flex-col gap-5',
                            'searchPlaceholder' => __('Search cakes…'),
                        ])
                        <div class="mt-6 flex flex-col gap-3 border-t border-stone-100 pt-5">
                            <button type="submit" class="min-h-[2.75rem] w-full rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2.5 text-sm font-bold text-white shadow-sm transition hover:shadow-md">
                                {{ __('Apply Filters') }}
                            </button>
                            @if($hasActiveFilters)
                                <a href="{{ route('products.index') }}" class="min-h-[2.75rem] w-full rounded-lg border border-stone-200 bg-stone-50 px-4 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800">
                                    {{ __('Clear all') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </details>
            <script>
                (function (panel) {
                    if (!panel) return;
                    var mobileQuery = window.matchMedia('(max-width: 1023px)');
                    function syncPanelOpenState() {
                        if (mobileQuery.matches) {
                            panel.removeAttribute('open');
                        } else {
                            panel.setAttribute('open', '');
                        }
                    }
                    syncPanelOpenState();
                    if (typeof mobileQuery.addEventListener === 'function') {
                        mobileQuery.addEventListener('change', syncPanelOpenState);
                    } else if (typeof mobileQuery.addListener === 'function') {
                        mobileQuery.addListener(syncPanelOpenState);
                    }
                })(document.querySelector('.catalog-filters-panel'));
            </script>

            {{-- Product results --}}
            <div class="catalog-results min-w-0 lg:col-start-2 lg:row-start-1">
                <div class="catalog-results-toolbar mb-4 flex flex-col gap-3 rounded-xl border border-stone-200/80 bg-white px-4 py-3 sm:flex-row sm:items-center sm:justify-between sm:px-5">
                    <p class="text-sm text-stone-600">
                        @if($products->total() > 0)
                            {{ __('Showing') }}
                            <span class="font-semibold text-stone-900">{{ $products->firstItem() }}–{{ $products->lastItem() }}</span>
                            {{ __('of') }}
                            <span class="font-semibold text-amber-600">{{ $products->total() }}</span>
                        @else
                            <span class="font-semibold text-stone-900">0</span> {{ __('products') }}
                        @endif
                    </p>
                    @if($hasActiveFilters)
                        <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center gap-1.5 self-start rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-200 sm:self-center">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            {{ __('Clear filters') }}
                        </a>
                    @endif
                </div>

                <div class="catalog-product-grid">
                    @forelse($products as $product)
                        @include('products._card', ['product' => $product])
                    @empty
                        <div class="catalog-empty col-span-full rounded-2xl border border-dashed border-amber-200/80 bg-white px-6 py-12 text-center sm:py-16">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-stone-900">{{ __('No products found') }}</h3>
                                    <p class="mt-2 text-sm text-stone-500">{{ __('Try different filters or browse the full catalog.') }}</p>
                                </div>
                                <a href="{{ route('products.index') }}" class="inline-flex min-h-[2.75rem] items-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow-md">
                                    {{ __('View all products') }}
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-8 sm:mt-10">{{ $products->links('components.pagination') }}</div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Catalog: mobile stack, desktop sidebar + grid */
    .catalog-layout {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.25rem;
        align-items: start;
    }
    @media (min-width: 1024px) {
        .catalog-layout {
            grid-template-columns: minmax(17.5rem, 20rem) minmax(0, 1fr);
            gap: 2rem;
        }
        .catalog-filters-panel {
            display: block;
        }
        .catalog-filters-panel > summary {
            display: none;
        }
        .catalog-filters-panel .catalog-filters-body {
            display: block !important;
            border-top: none;
        }
        /* Mobile: collapsed by default; desktop: always show filter form */
        details.catalog-filters-panel:not([open]) .catalog-filters-body {
            display: block !important;
        }
        .catalog-filters-panel .catalog-filters-chevron {
            display: none;
        }
    }

    #product-filters .product-filters-field + .product-filters-field {
        margin-top: 0;
    }
    #product-filters .product-filters-search {
        padding-bottom: 1.25rem;
        margin-bottom: 0.25rem;
        border-bottom: 1px solid rgb(245 245 244);
    }

    /* Product grid: 1 → 2 → 3 columns */
    .catalog-product-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    @media (min-width: 480px) {
        .catalog-product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }
    }
    @media (min-width: 768px) {
        .catalog-product-grid {
            gap: 1.25rem;
        }
    }
    @media (min-width: 1024px) {
        .catalog-product-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
    @media (min-width: 1280px) {
        .catalog-product-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1.5rem;
        }
    }

    /* Listing cards: consistent height in grid */
    .catalog-product-grid > a {
        height: 100%;
    }
</style>
@endpush
