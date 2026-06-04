@extends('layouts.app')

@section('title', __('Products') . ' – ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Page Header --}}
<section class="relative min-h-[30vh] flex items-center overflow-hidden bg-amber-50 py-16 lg:py-24">
    <!-- Soft background glow effect -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-amber-200/40 to-orange-200/40 rounded-full blur-3xl opacity-60 pointer-events-none"></div>
    
    <div class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="font-display text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-stone-900 mb-6 drop-shadow-sm">{{ __('Our Products') }}</h1>
        <p class="text-lg sm:text-xl text-stone-600 max-w-2xl mx-auto leading-relaxed">{{ __('Explore our complete collection of handcrafted, delicious cakes') }}</p>
    </div>
</section>

{{-- Filter and Products Section --}}
<section class="bg-white py-12 lg:py-20 relative">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 relative z-10">
        {{-- Filter Section --}}
        <div class="mb-16 rounded-3xl border border-amber-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <div class="flex items-center gap-3 mb-6">
                <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                <h2 class="text-2xl font-bold text-stone-900">{{ __('Filter Products') }}</h2>
            </div>
            <form method="get" action="{{ route('products.index') }}" class="space-y-6" id="product-filters">
                @include('products.partials._filters-fields', [
                    'inputClass' => 'w-full rounded-xl border border-amber-200/60 bg-amber-50/30 px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all',
                    'selectClass' => 'w-full rounded-xl border border-amber-200/60 bg-amber-50/30 px-4 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all',
                    'multiSelectClass' => 'w-full rounded-xl border border-amber-200/60 bg-amber-50/30 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all',
                    'priceInputClass' => 'w-full rounded-xl border border-amber-200/60 bg-amber-50/30 px-3 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all',
                    'labelClass' => 'mb-2 block text-sm font-medium text-stone-700',
                    'gridClass' => 'grid gap-6 sm:grid-cols-2 lg:grid-cols-3',
                ])
                <div class="flex flex-wrap items-center gap-4 pt-4 border-t border-stone-100">
                    <button type="submit" class="rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-3.5 font-bold text-white transition-all duration-300 hover:shadow-[0_8px_20px_rgb(217,119,6,0.25)] hover:-translate-y-0.5">
                        {{ __('Apply Filters') }}
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
                    <a href="{{ route('products.index') }}" class="rounded-full border-2 border-stone-200 bg-white px-8 py-3 font-bold text-stone-600 transition-colors hover:border-stone-300 hover:text-stone-900">{{ __('Clear All') }}</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Results Count --}}
        <div class="mb-8 flex items-center justify-between">
            <h3 class="text-xl font-bold text-stone-900">{{ __('Our Collection') }}</h3>
            @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
                <div class="text-sm font-medium text-stone-500 bg-stone-100 px-4 py-1.5 rounded-full">
                    {{ __('Showing') }} <span class="text-amber-600 font-bold">{{ $products->total() }}</span> {{ __('products') }}
                </div>
            @endif
        </div>

        {{-- Products Grid --}}
        <div class="product-list-grid grid gap-8 sm:gap-10 sm:grid-cols-2 lg:grid-cols-4">
            @forelse($products as $product)
                @include('products._card', ['product' => $product])
            @empty
                <div class="col-span-full rounded-3xl border border-dashed border-stone-200 bg-stone-50/50 p-16 text-center">
                    <div class="mx-auto flex max-w-md flex-col items-center gap-6">
                        <div class="flex h-24 w-24 items-center justify-center rounded-full bg-white shadow-sm border border-stone-100">
                            <svg class="h-10 w-10 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-stone-900">{{ __('No products found') }}</h3>
                            <p class="mt-3 text-stone-500">{{ __('We couldn\'t find anything matching your current filters. Try adjusting them or clear all filters.') }}</p>
                        </div>
                        <div class="flex gap-4 mt-2 justify-center">
                            <a href="{{ route('products.index') }}" class="rounded-full border-2 border-stone-200 bg-white px-8 py-3 font-bold text-stone-600 transition-colors hover:border-stone-300 hover:text-stone-900">
                                {{ __('Clear Filters') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-16">{{ $products->links('components.pagination') }}</div>
        @endif
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Keep desktop at 4 columns even when lg utility classes are missing. */
    @media (min-width: 1024px) {
        .product-list-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
    }
</style>
@endpush