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

<section class="bg-stone-50 py-4">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <nav class="flex items-center space-x-2 text-sm">
            <a href="{{ route('home') }}" class="text-stone-500 transition-colors hover:text-amber-600">{{ __('Home') }}</a>
            <span class="text-stone-300">/</span>
            <a href="{{ route('products.index') }}" class="text-stone-500 transition-colors hover:text-amber-600">{{ __('Products') }}</a>
            <span class="text-stone-300">/</span>
            <span class="font-medium text-stone-900">{{ $product->name_en }}</span>
        </nav>
    </div>
</section>

<section class="bg-white py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-12 lg:grid-cols-2">
            @include('products.partials._gallery', ['product' => $product])

            <div class="space-y-6">
                <div>
                    <h1 class="text-4xl font-bold tracking-tight text-stone-900 lg:text-5xl">{{ $product->name_en }}</h1>
                    @if($product->category)
                        <p class="mt-2 text-lg text-amber-600">{{ $product->category->name_en }}</p>
                    @endif
                    @include('products.partials._product-sku', ['product' => $product])
                </div>

                <div class="rounded-2xl border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 p-6">
                    @if(!empty($hasVariants) && $variantChoices->isNotEmpty())
                        <p class="text-sm font-medium text-stone-600" id="pdp-price-label">{{ __('Starting from') }}</p>
                        <p class="mt-1 text-4xl font-bold text-stone-900" id="product-unit-price">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
                        <p class="mt-4 text-sm font-semibold text-stone-700">{{ __('Select weight') }}</p>
                        <div
                            class="variant-picker mt-2 flex flex-wrap gap-1"
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
                                <div class="flex flex-col items-center" data-variant-option>
                                    <button type="button" data-variant-id="{{ $choice['id'] }}" class="rounded-full border border-amber-300 bg-white px-3 py-1.5 text-sm font-semibold text-stone-800 hover:bg-amber-50" aria-pressed="{{ $isSelected ? 'true' : 'false' }}">{{ $choice['label'] }}</button>
                                    @if(!empty($choice['person_capacity_label']))
                                        <span
                                            data-variant-capacity
                                            class="text-xs text-stone-600 {{ $isSelected ? '' : 'hidden' }}"
                                        >{{ $choice['person_capacity_label'] }}</span>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-sm font-medium text-stone-600">{{ __('Price') }}</p>
                        <p class="mt-1 text-4xl font-bold text-stone-900">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
                    @endif
                </div>

                @if($product->short_description)
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">{{ __('Quick Overview') }}</h2>
                        <p class="mt-2 text-lg text-stone-600">{{ $product->short_description }}</p>
                    </div>
                @endif

                @if($product->flavors->isNotEmpty())
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">{{ __('Available flavors') }}</h2>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($product->flavors as $flavor)
                                <span class="inline-flex rounded-full border border-rose-200 bg-rose-50 px-3 py-1 text-sm font-semibold text-rose-800">{{ $flavor->name_en }}</span>
                            @endforeach
                        </div>
                        <p class="mt-2 text-sm text-stone-500">{{ __('Choose your flavor when placing an order.') }}</p>
                    </div>
                @endif

                @if($product->ingredients)
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">{{ __('Ingredient highlights') }}</h2>
                        <ul class="mt-2 space-y-1 text-stone-600">
                            @foreach(array_filter(array_map('trim', preg_split('/[\r\n,]+/', $product->ingredients))) as $item)
                                <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 shrink-0 rounded-full bg-amber-500"></span>{{ $item }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($product->description_en)
                    <div>
                        <h2 class="text-xl font-semibold text-stone-900">{{ __('Description') }}</h2>
                        <div class="prose prose-lg mt-2 max-w-none text-stone-600">
                            {!! nl2br(e($product->description_en)) !!}
                        </div>
                    </div>
                @endif

                @if(trim(settings('product_note') ?? '') !== '')
                    <div class="flex gap-3 rounded-xl border border-stone-200 bg-stone-50 px-4 py-3">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-stone-300 text-xs font-bold text-stone-600">i</span>
                        <p class="text-sm leading-relaxed text-stone-600">{{ settings('product_note') }}</p>
                    </div>
                @endif

                @include('products.partials._whatsapp-customize-help', ['product' => $product])

                <div class="pt-6">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-5">
                        <a href="{{ route('order.place', $product) }}{{ !empty($hasVariants) && $defaultVariant ? '?product_variant_id='.$defaultVariant->id : '' }}" id="pdp-order-link" data-base-url="{{ route('order.place', $product) }}" class="inline-flex w-full shrink-0 items-center justify-center rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-semibold text-white shadow-lg transition-all duration-200 hover:from-amber-600 hover:to-orange-600 hover:shadow-xl sm:w-auto">
                            {{ __('Order this cake') }}
                        </a>
                        @include('products.partials._order-delivery-label', [
                            'product' => $product,
                            'class' => 'text-stone-700',
                            'iconClass' => 'text-stone-500',
                            'valueClass' => 'text-red-600',
                        ])
                    </div>
                    @include('products.partials._order-delivery-footnote', ['product' => $product, 'class' => 'text-stone-500'])
                </div>
            </div>
        </div>
    </div>
</section>

@if($related->isNotEmpty())
<section class="border-t border-amber-100/80 bg-gradient-to-b from-amber-50/50 to-white py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="mb-12 text-center">
            <h2 class="text-3xl font-bold text-stone-900 sm:text-4xl">{{ __('You might also love') }}</h2>
        </div>
        <div class="related-products-grid grid items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($related as $p)
                <div class="h-full min-h-0 min-w-0 overflow-hidden">
                    @include('products._card', ['product' => $p])
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endsection
