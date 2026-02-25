@extends('layouts.app')

@section('title', __('Products') . ' – ' . (settings('site_name') ?: config('app.name')))

@section('content')
{{-- Page Header --}}
<section class="bg-gradient-to-br from-amber-50 via-orange-50 to-amber-100 py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">{{ __('Our Products') }}</h1>
        <p class="mt-4 text-xl text-gray-600">{{ __('Browse our complete collection of delicious cakes') }}</p>
    </div>
</section>

{{-- Filter and Products Section --}}
<section class="bg-white py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Filter Section --}}
        <div class="mb-12 rounded-2xl border border-gray-200 bg-white p-6 shadow-lg">
            <h2 class="mb-6 text-xl font-semibold text-gray-900">{{ __('Filter Products') }}</h2>
            <form method="get" action="{{ route('products.index') }}" class="space-y-4" id="product-filters">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search by name or ingredients...') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                </div>
                <div>
                    <label for="category_id" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
                    <select name="category_id" id="category_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        <option value="">{{ __('All Categories') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name_en }}</option>
                        @endforeach
                    </select>
                </div>
                @if($priceRange && $priceRange->min_price !== null)
                <div>
                    <label class="mb-2 block text-sm font-medium text-gray-700">{{ __('Price range') }}</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="price_min" id="price_min" value="{{ request('price_min') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Min') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        <span class="text-gray-400">–</span>
                        <input type="number" name="price_max" id="price_max" value="{{ request('price_max') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Max') }}" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                    </div>
                </div>
                @endif
                <div>
                    <label for="sort" class="mb-2 block text-sm font-medium text-gray-700">{{ __('Sort By') }}</label>
                    <select name="sort" id="sort" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20">
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>{{ __('Name A–Z') }}</option>
                        <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('Name Z–A') }}</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: Low to High') }}</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: High to Low') }}</option>
                        <option value="newest" @selected(request('sort') === 'newest')>{{ __('Newest First') }}</option>
                    </select>
                </div>
                </div>
                <div class="flex flex-wrap items-center gap-3">
                    <button type="submit" class="rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 font-semibold text-white transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg">
                        {{ __('Apply Filters') }}
                    </button>
                    @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max']))
                    <a href="{{ route('products.index') }}" class="rounded-lg border border-gray-300 bg-white px-5 py-3 font-medium text-gray-700 transition-colors hover:bg-gray-50">{{ __('Clear') }}</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Results Count --}}
        @if(request()->hasAny(['search', 'category_id', 'sort', 'price_min', 'price_max']))
            <div class="mb-6 text-sm text-gray-600">
                {{ __('Showing') }} <span class="font-semibold text-gray-900">{{ $products->total() }}</span> {{ __('products') }}
            </div>
        @endif

        {{-- Products Grid --}}
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
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
                            <h3 class="text-xl font-semibold text-gray-900">{{ __('No products found') }}</h3>
                            <p class="mt-2 text-gray-600">{{ __('Try adjusting your search or filter criteria.') }}</p>
                        </div>
                        <div class="flex gap-4">
                            <a href="{{ route('products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-6 py-3 font-medium text-gray-700 transition-colors duration-200 hover:bg-gray-50">
                                {{ __('Clear Filters') }}
                            </a>
                            <a href="{{ route('contact.index') }}" class="inline-flex items-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 font-semibold text-white transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-lg">
                                {{ __('Contact Us') }}
                            </a>
                        </div>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($products->hasPages())
            <div class="mt-12 flex justify-center">{{ $products->links() }}</div>
        @endif
    </div>
</section>
@endsection
