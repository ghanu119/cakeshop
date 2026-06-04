@extends('layouts.app')

@section('title', __('Products') . ' – ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Yellowish-cream banner strip (visual break below header) --}}
<div class="h-2 bg-[#ebe8e0] border-b border-stone-200/50"></div>

{{-- Page title: centered --}}
<section class="bg-[#f5f5f0] py-10 lg:py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 text-center">
        <h1 class="heading-display text-4xl sm:text-5xl text-stone-900">{{ __('Our Products') }}</h1>
        <p class="mt-3 text-lg sm:text-xl text-stone-600">{{ __('Browse our complete collection of delicious cakes') }}</p>
    </div>
</section>

{{-- Filter card + products grid (white background) --}}
<section class="bg-white py-10 lg:py-14">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Filter card: white, rounded, shadow --}}
        <div class="lumiere-filter-card mb-12 rounded-2xl border border-stone-200/80 bg-white p-6 shadow-[0_4px_20px_rgba(90,90,64,0.06)]">
            <h2 class="mb-5 text-lg font-semibold text-stone-900">{{ __('Filter Products') }}</h2>
            <form method="get" action="{{ route('products.index') }}" class="space-y-4" id="product-filters">
                @include('products.partials._filters-fields', [
                    'inputClass' => 'input-modern w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 placeholder:text-stone-400 focus:border-[#5A5A40] focus:ring-2 focus:ring-[#5A5A40]/20',
                    'selectClass' => 'input-modern w-full rounded-xl border border-stone-200 bg-white px-4 py-3 text-stone-900 focus:border-[#5A5A40] focus:ring-2 focus:ring-[#5A5A40]/20',
                    'multiSelectClass' => 'input-modern w-full rounded-xl border border-stone-200 bg-white text-stone-900 focus:border-[#5A5A40] focus:ring-2 focus:ring-[#5A5A40]/20',
                    'priceInputClass' => 'w-full rounded-xl border border-stone-200 bg-white px-3 py-2.5 text-stone-900 placeholder:text-stone-400 focus:border-[#5A5A40] focus:ring-2 focus:ring-[#5A5A40]/20',
                    'labelClass' => 'mb-1.5 block text-sm font-medium text-stone-700',
                    'gridClass' => 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3',
                ])
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <button type="submit" class="lumiere-btn-apply rounded-xl px-6 py-3 font-semibold text-white shadow-md transition-all hover:shadow-lg hover:brightness-105" style="background: linear-gradient(135deg, #b8956e 0%, #a08050 100%); color: #fff;">
                        {{ __('Apply Filters') }}
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
                    <a href="{{ route('products.index') }}" class="rounded-xl border border-stone-300 bg-white px-5 py-3 font-medium text-stone-700 transition-colors hover:bg-stone-50">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>
        </div>

        @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max', 'flavor_ids', 'weight_ids']))
        <div class="mb-6 text-sm text-stone-600">
            {{ __('Showing') }} <span class="font-semibold text-stone-900">{{ $products->total() }}</span> {{ __('products') }}
        </div>
        @endif

        {{-- Product grid --}}
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
            @forelse($products as $product)
                @include('products._card', ['product' => $product])
            @empty
                <div class="col-span-full rounded-2xl border border-stone-200/80 bg-white p-16 shadow-sm">
                    <div class="mx-auto flex max-w-md flex-col items-center gap-6 text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-stone-100 text-stone-400">
                            <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-semibold text-stone-900">{{ __('No products found') }}</h3>
                            <p class="mt-2 text-stone-600">{{ __('Try adjusting your search or filter criteria.') }}</p>
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('products.index') }}" class="rounded-xl border border-stone-300 bg-white px-6 py-3 font-medium text-stone-700 transition-colors hover:bg-stone-50">
                                {{ __('Clear Filters') }}
                            </a>
                            <a href="{{ route('contact.index') }}" class="lumiere-btn-apply inline-flex rounded-xl px-6 py-3 font-semibold shadow-md hover:shadow-lg" style="background: linear-gradient(135deg, #b8956e 0%, #a08050 100%); color: #fff;">
                                {{ __('Contact Us') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        @if($products->hasPages())
        <div class="mt-12">{{ $products->links('pagination.tailwind') }}</div>
        @endif
    </div>
</section>
@endsection
