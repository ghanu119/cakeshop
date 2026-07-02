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
    $catalogClearUrl = $category
        ? route('products.category', ['slug' => $category->slug])
        : route('products.index');
@endphp

{{-- Page Header --}}
<section class="bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 py-12 lg:py-16" data-testid="{{ $category ? 'category-page' : 'products-page' }}">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        @if($category)
            <nav class="mb-4 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm font-medium text-gray-600" aria-label="{{ __('Breadcrumb') }}">
                <a href="{{ route('home') }}" class="hover:text-amber-700">{{ __('Home') }}</a>
                <span aria-hidden="true">/</span>
                <a href="{{ route('products.index') }}" class="hover:text-amber-700">{{ __('Products') }}</a>
                <span aria-hidden="true">/</span>
                <span class="text-gray-900">{{ $category->name_en }}</span>
            </nav>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">{{ $category->name_en }}</h1>
            <p class="mt-4 text-xl text-gray-600">{{ __('Discover handcrafted :category made fresh for every celebration.', ['category' => strtolower($category->name_en)]) }}</p>
        @else
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">{{ __('Our Products') }}</h1>
            <p class="mt-4 text-xl text-gray-600">{{ __('Browse our complete collection of delicious cakes') }}</p>
        @endif
    </div>
</section>

{{-- Filter and Products Section --}}
<section class="bg-white py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Filter Section --}}
        <div class="mb-12 rounded-2xl border border-gray-200 bg-white p-6 shadow-lg">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">{{ __('Filter Products') }}</h2>
            <form method="get" action="{{ $catalogAction }}" class="space-y-4" id="product-filters">
                @include('products.partials._filters-fields', [
                    'selectedCategoryId' => $category?->id,
                ])
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 font-semibold text-white transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg">
                        {{ __('Apply Filters') }}
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
                    <a href="{{ $catalogClearUrl }}" class="rounded-lg border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-50">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Results Count --}}
        @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
            <div class="mb-6 text-sm text-gray-600">
                {{ __('Showing') }} <span class="font-semibold text-gray-900">{{ $products->total() }}</span> {{ __('products') }}
            </div>
        @endif

        {{-- Products Grid --}}
        <div data-product-autoload data-next-page-url="{{ $products->nextPageUrl() ?? '' }}">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3" data-product-grid>
            @forelse($products as $product)
                @include('products._card', ['product' => $product])
            @empty
                <div class="col-span-full rounded-2xl border border-gray-200 bg-white p-16 shadow-sm">
                    <div class="mx-auto flex max-w-md flex-col items-center gap-6 text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-gradient-to-br from-amber-100 to-orange-100">
                            <svg class="h-10 w-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            @if($category)
                                <h3 class="text-xl font-semibold text-gray-900">{{ __('No products in this category yet') }}</h3>
                                <p class="mt-2 text-gray-600">{{ __('Check back soon or browse our full catalog.') }}</p>
                            @else
                                <h3 class="text-xl font-semibold text-gray-900">{{ __('No products found') }}</h3>
                                <p class="mt-2 text-gray-600">{{ __('Try adjusting your search or filter criteria.') }}</p>
                            @endif
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                                {{ __('View all products') }}
                            </a>
                            @unless($category)
                            <a href="{{ route('contact.index') }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 font-semibold text-white transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg">
                                {{ __('Contact Us') }}
                            </a>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-8 hidden" data-product-autoload-status aria-live="polite">
                <div class="mx-auto flex max-w-xs items-center justify-center gap-3 rounded-full border border-amber-200 bg-gradient-to-r from-amber-50 via-orange-50 to-rose-50 px-5 py-3 text-sm font-medium text-amber-900 shadow-sm">
                    <div class="flex items-center gap-1.5" data-product-autoload-loader>
                        <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-amber-400 [animation-delay:-0.3s]"></span>
                        <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-orange-400 [animation-delay:-0.15s]"></span>
                        <span class="h-2.5 w-2.5 animate-bounce rounded-full bg-rose-400"></span>
                    </div>
                    <span data-product-autoload-message>{{ __('Baking more cakes...') }}</span>
                </div>
            </div>
            <div class="mt-6 h-1 w-full" data-product-autoload-sentinel aria-hidden="true"></div>
            <div class="mt-12 flex justify-center" data-product-pagination>{{ $products->links() }}</div>
        @endif
        </div>
    </div>
</section>
@endsection
