@extends('layouts.app')

@section('title', ($category ?? null)
    ? $category->name_en . ' – ' . (settings('site_name') ?: config('app.name'))
    : __('Products') . ' – ' . (settings('site_name') ?: config('app.name')))

@if($category ?? null)
@push('meta')
    @include('partials.meta-category', ['category' => $category, 'products' => $products])
    @include('partials.json-ld-category', ['category' => $category, 'products' => $products])
@endpush
@endif

@section('content')
@php
    $category = $category ?? null;
    $catalogAction = $category
        ? route('products.category', $category->slug)
        : route('products.index');
    $catalogClearParams = request()->only('sort');
    $catalogClearUrl = $category
        ? route('products.category', array_merge(['slug' => $category->slug], $catalogClearParams))
        : route('products.index', $catalogClearParams);
    $hasActiveFilters = request()->filled('search')
        || (! $category && request()->filled('category_id'))
        || request()->filled('price_min')
        || request()->filled('price_max')
        || request()->filled('flavor_ids')
        || request()->filled('weight_ids');
    $sortSelectClass = 'min-w-[11rem] rounded-lg border border-stone-200 bg-white py-2 pl-3 pr-8 text-sm text-stone-900 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $filterInputClass = 'w-full min-h-[3rem] rounded-xl border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 placeholder:text-stone-400 shadow-sm focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $filterSelectClass = $filterInputClass;
    $filterLabelClass = 'mb-2.5 block text-sm font-medium text-stone-800';
    $categoryDescription = $category
        ? __('Discover handcrafted :category made fresh for every celebration.', ['category' => strtolower($category->name_en)])
        : null;
@endphp

<section class="bg-stone-50 py-5 sm:py-6 lg:py-8" data-testid="{{ $category ? 'category-page' : 'products-page' }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <header class="mb-5 sm:mb-6">
            <nav class="mb-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-stone-500" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="transition-colors hover:text-amber-600">{{ __('Home') }}</a>
                <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                @if($category)
                    <a href="{{ route('products.index') }}" class="transition-colors hover:text-amber-600">{{ __('Products') }}</a>
                    <svg class="h-4 w-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    <span class="text-stone-900">{{ $category->name_en }}</span>
                @else
                    <span class="text-stone-900">{{ __('Products') }}</span>
                @endif
            </nav>
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between sm:gap-4">
                <div>
                    @if($category)
                        <h1 class="font-display text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">
                            {{ $category->name_en }}
                        </h1>
                        <p class="mt-1 max-w-2xl text-sm text-stone-600">{{ $categoryDescription }}</p>
                    @else
                        <h1 class="font-display text-2xl font-bold tracking-tight text-stone-900 sm:text-3xl">
                            {{ __('Our') }} <span class="text-amber-600">{{ __('Products') }}</span>
                        </h1>
                        <p class="mt-1 text-sm text-stone-600">{{ __('Explore our complete collection of handcrafted, delicious cakes') }}</p>
                    @endif
                </div>
                @if($products->total() > 0)
                    <p class="text-sm font-medium text-stone-500">
                        <span class="font-semibold text-amber-600">{{ $products->total() }}</span>
                        @if($category)
                            {{ $products->total() === 1 ? __('product') : __('products') }}
                        @else
                            {{ __('in catalog') }}
                        @endif
                    </p>
                @endif
            </div>
        </header>

        <div class="catalog-layout">
            <details @if($hasActiveFilters) open @endif class="hidden catalog-filters-panel group rounded-2xl border border-amber-100/90 bg-white shadow-sm">
                <summary class="catalog-filters-summary flex cursor-pointer list-none items-center justify-between gap-3 px-4 py-3.5 text-sm font-semibold text-stone-900 sm:px-5 sm:py-4 [&::-webkit-details-marker]:hidden">
                    <span class="inline-flex items-center gap-2">
                        <svg class="h-5 w-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                        {{ __('Filters') }}
                        @if($hasActiveFilters)
                            <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-semibold text-amber-800">{{ __('Active') }}</span>
                        @endif
                    </span>
                    <svg class="catalog-filters-chevron h-5 w-5 shrink-0 text-stone-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                </summary>
                <div class="catalog-filters-body border-t border-stone-100 px-5 pb-5 pt-4 sm:px-6 sm:pb-6 sm:pt-5">
                    <form method="get" action="{{ $catalogAction }}" id="product-filters">
                        <input type="hidden" name="sort" value="{{ request('sort', 'name_asc') }}">
                        @include('products.partials._filters-fields', [
                            'inputClass' => $filterInputClass,
                            'selectClass' => $filterSelectClass,
                            'multiSelectClass' => $filterSelectClass,
                            'priceInputClass' => $filterInputClass,
                            'labelClass' => $filterLabelClass,
                            'fieldsGridClass' => 'product-filters-grid grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4',
                            'searchPlaceholder' => __('Search cakes…'),
                            'showSort' => false,
                            'selectedCategoryId' => $category?->id,
                        ])
                        <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-stone-100 pt-5">
                            <button type="submit" class="min-h-[2.75rem] rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:shadow-md">
                                {{ __('Apply Filters') }}
                            </button>
                            @if($hasActiveFilters)
                                <a href="{{ $catalogClearUrl }}" class="min-h-[2.75rem] rounded-lg border border-stone-200 bg-stone-50 px-6 py-2.5 text-center text-sm font-semibold text-stone-700 transition hover:border-amber-200 hover:bg-amber-50 hover:text-amber-800">
                                    {{ __('Clear all') }}
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </details>

            <div class="catalog-results min-w-0">
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
                    <div class="flex flex-wrap items-center gap-3 self-start sm:self-center">
                        <form method="get" action="{{ $catalogAction }}" id="product-sort" class="inline-flex items-center gap-2">
                            @foreach(request()->except(['sort', 'page']) as $key => $value)
                                @if(is_array($value))
                                    @foreach($value as $item)
                                        <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                                    @endforeach
                                @else
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach
                            <label for="catalog-sort" class="text-sm font-medium text-stone-600">{{ __('Sort') }}</label>
                            @include('products.partials._sort-select', [
                                'selectId' => 'catalog-sort',
                                'selectClass' => $sortSelectClass,
                                'autoSubmit' => true,
                            ])
                        </form>
                        @if($hasActiveFilters)
                            <a href="{{ $catalogClearUrl }}" class="inline-flex items-center justify-center gap-1.5 rounded-full bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-900 transition hover:bg-amber-200">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                {{ __('Clear filters') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div data-product-autoload data-next-page-url="{{ $products->nextPageUrl() ?? '' }}">
                <div class="catalog-product-grid" data-product-grid>
                    @forelse($products as $product)
                        @include('products._card', ['product' => $product])
                    @empty
                        <div class="catalog-empty col-span-full rounded-2xl border border-dashed border-amber-200/80 bg-white px-6 py-12 text-center sm:py-16">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-4">
                                <div class="flex h-16 w-16 items-center justify-center rounded-full bg-amber-50 text-amber-500">
                                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                                </div>
                                <div>
                                    @if($category)
                                        <h2 class="text-lg font-bold text-stone-900">{{ __('No products in this category yet') }}</h2>
                                        <p class="mt-2 text-sm text-stone-500">{{ __('Check back soon or browse our full catalog.') }}</p>
                                    @else
                                        <h3 class="text-lg font-bold text-stone-900">{{ __('No products found') }}</h3>
                                        <p class="mt-2 text-sm text-stone-500">{{ __('Try different filters or browse the full catalog.') }}</p>
                                    @endif
                                </div>
                                <a href="{{ route('products.index') }}" class="inline-flex min-h-[2.75rem] items-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-2.5 text-sm font-bold text-white shadow-sm hover:shadow-md">
                                    {{ __('View all products') }}
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($products->hasPages())
                    <div class="mt-6 hidden" data-product-autoload-status aria-live="polite">
                        <div class="mx-auto flex max-w-xs items-center justify-center gap-3 rounded-full border border-amber-200/80 bg-gradient-to-r from-amber-50 via-orange-50 to-stone-50 px-5 py-3 text-sm font-semibold text-amber-900 shadow-sm">
                            <div class="flex items-center gap-1.5" data-product-autoload-loader>
                                <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-amber-400 [animation-delay:-0.3s]"></span>
                                <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-orange-400 [animation-delay:-0.15s]"></span>
                                <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-stone-400"></span>
                            </div>
                            <span data-product-autoload-message>{{ __('Baking more cakes...') }}</span>
                        </div>
                    </div>
                    <div class="mt-5 h-1 w-full" data-product-autoload-sentinel aria-hidden="true"></div>
                    <div class="mt-8 sm:mt-10" data-product-pagination>{{ $products->links('components.pagination') }}</div>
                @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .catalog-layout {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        align-items: stretch;
    }

    #product-filters .product-filters-field + .product-filters-field {
        margin-top: 0;
    }
    #product-filters .product-filters-search {
        padding-bottom: 1.25rem;
        margin-bottom: 0.25rem;
        border-bottom: 1px solid rgb(245 245 244);
    }

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
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (min-width: 1280px) {
        .catalog-product-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 1.5rem;
        }
    }

    .catalog-product-grid > a {
        height: 100%;
    }
</style>
@endpush
