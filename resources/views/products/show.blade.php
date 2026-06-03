@extends('layouts.app')

@section('title', ($product->meta_title ?: $product->name_en) . ' - ' . (settings('site_name') ?: config('app.name')))

@push('meta')
    @include('partials.og-product', ['product' => $product])
    @include('partials.json-ld-product', ['product' => $product])
@endpush

@section('content')
@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $hasVariants = $hasVariants ?? false;
    $variantChoices = $variantChoices ?? collect();
    $initialVariantId = (int) request('product_variant_id', $defaultVariant?->id);
@endphp

@if($hasVariants)
    @include('order.partials._picker-styles')
@endif

{{-- Breadcrumb --}}
<section class="bg-gray-50 py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="{{ route('home') }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <a href="{{ route('products.index') }}" class="text-gray-500 hover:text-gray-700 transition-colors">{{ __('Products') }}</a>
            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <span class="text-gray-900 font-medium">{{ $product->name_en }}</span>
        </nav>
    </div>
</section>

{{-- Product Detail Section --}}
<section class="bg-white py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            {{-- Product Image --}}
            <div class="space-y-4">
                <div class="overflow-hidden rounded-2xl bg-gray-100 shadow-lg">
                    @if($product->getFirstMediaUrl('product_images', 'large'))
                        <img src="{{ $product->getFirstMediaUrl('product_images', 'large') }}" alt="{{ $product->name_en }}" class="aspect-square w-full object-cover" />
                    @else
                        <div class="aspect-square w-full flex items-center justify-center bg-gradient-to-br from-amber-100 to-orange-100 text-gray-400">
                            <svg class="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
                @if($product->is_highlight || $product->is_trending || $product->is_featured)
                    <div class="flex gap-2">
                        @if($product->is_highlight)
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-4 py-2 text-sm font-semibold text-white">{{ __('Highlight') }}</span>
                        @endif
                        @if($product->is_trending)
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-red-500 to-pink-500 px-4 py-2 text-sm font-semibold text-white">{{ __('Trending') }}</span>
                        @endif
                        @if($product->is_featured)
                            <span class="inline-flex items-center rounded-full bg-gradient-to-r from-blue-500 to-purple-500 px-4 py-2 text-sm font-semibold text-white">{{ __('Featured') }}</span>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Product Info --}}
            <div class="space-y-6">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-gray-900 lg:text-5xl">{{ $product->name_en }}</h1>
                    @if($product->category)
                        <p class="mt-2 text-lg text-gray-600">{{ $product->category->name_en }}</p>
                    @endif
                </div>

                <div class="rounded-2xl border-2 border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6">
                    @if(!empty($hasVariants) && $variantChoices->isNotEmpty())
                        <p class="text-sm font-medium text-gray-600" id="pdp-price-label">{{ __('Starting from') }}</p>
                        <p class="mt-1 text-4xl font-bold text-gray-900" id="product-unit-price">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
                        <p class="mt-4 text-sm font-semibold text-gray-700">{{ __('Select weight') }}</p>
                        <div
                            class="mt-2 flex flex-wrap gap-2 variant-picker"
                            data-variant-picker
                            data-choices="{{ $variantChoices->toJson() }}"
                            data-initial-variant-id="{{ request('product_variant_id', $defaultVariant?->id) }}"
                            data-currency-symbol="{{ $symbol }}"
                            data-unit-price-target="#product-unit-price"
                            data-order-link-target="#pdp-order-link"
                            data-order-link-base-url="{{ route('order.place', $product) }}"
                            role="radiogroup"
                            aria-label="{{ __('Weight') }}"
                        >
                            @foreach($variantChoices as $choice)
                                @php $isSelected = (string) $initialVariantId === (string) $choice['id']; @endphp
                                <button type="button" data-variant-id="{{ $choice['id'] }}" class="rounded-full border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-gray-800 hover:bg-amber-50" aria-pressed="{{ $isSelected ? 'true' : 'false' }}">{{ $choice['label'] }}</button>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm font-medium text-gray-600">{{ __('Price') }}</p>
                        <p class="mt-1 text-4xl font-bold text-gray-900">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
                    @endif
                </div>

                @if($product->short_description)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Quick Overview') }}</h2>
                        <p class="mt-2 text-lg text-gray-600">{{ $product->short_description }}</p>
                    </div>
                @endif

                @if($product->flavors->isNotEmpty())
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Available flavors') }}</h2>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($product->flavors as $flavor)
                                <span class="inline-flex rounded-full bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-800 border border-rose-200">{{ $flavor->name_en }}</span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-sm text-gray-500">{{ __('Choose your flavor when placing an order.') }}</p>
                    </div>
                @endif

                @if($product->ingredients)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Ingredient highlights') }}</h2>
                        <ul class="mt-2 space-y-1 text-gray-600">
                            @foreach(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $product->ingredients))) as $item)
                                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-amber-500 flex-shrink-0"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->description_en)
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ __('Description') }}</h2>
                        <div class="mt-2 prose prose-lg max-w-none text-gray-600">
                            {!! nl2br(e($product->description_en)) !!}
                        </div>
                    </div>
                @endif

                @if(trim(settings('product_note') ?? '') !== '')
                    <div class="rounded-xl border border-gray-200 bg-gray-50 px-4 py-3 flex gap-3">
                        <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-gray-300 text-gray-600 text-xs font-bold">i</span>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ settings('product_note') }}</p>
                    </div>
                @endif

                <div class="pt-6">
                    <a href="{{ route('order.place', $product) }}{{ !empty($hasVariants) && $defaultVariant ? '?product_variant_id='.$defaultVariant->id : '' }}" id="pdp-order-link" data-base-url="{{ route('order.place', $product) }}" class="inline-flex w-full items-center justify-center rounded-lg bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-xl hover:scale-105 lg:w-auto">
                        <svg class="mr-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        {{ __('Order this cake') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Related Products Section (best practice: same category, random order, fallback to others, limited to 4) --}}
@if($related->isNotEmpty())
<section class="border-t border-amber-100/80 bg-gradient-to-b from-amber-50/50 to-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="badge-warm mb-4">{{ __('Related') }}</span>
            <h2 class="heading-display text-3xl sm:text-4xl">{{ __('You might also love') }}</h2>
            <p class="mx-auto mt-3 max-w-2xl text-lg text-stone-600">{{ __('Explore more delicious options from our collection') }}</p>
        </div>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($related as $p)
                @include('products._card', ['product' => $p])
            @endforeach
        </div>
        <div class="mt-12 text-center">
            <a href="{{ route('products.index') }}" class="btn-primary-modern inline-flex items-center px-8 py-4 text-base font-bold border-0 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2 rounded-xl">
                {{ __('All Products') }}
                <svg class="ml-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </div>
</section>
@endif
@endsection
