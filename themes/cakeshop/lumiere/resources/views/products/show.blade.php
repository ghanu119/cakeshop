@extends('layouts.app')

@section('title', ($product->meta_title ?: $product->name_en) . ' - ' . (settings('site_name') ?: config('app.name')))

@push('meta')
    @include('partials.og-product', ['product' => $product])
    @include('partials.json-ld-product', ['product' => $product])
@endpush

@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : ($currency === 'USD' ? '$' : $currency . ' ');
    $ingredientList = $product->ingredients
        ? array_filter(array_map('trim', preg_split('/[\r\n,]+/', $product->ingredients)))
        : [];
@endphp

@section('content')
{{-- Breadcrumb: minimal, cream background --}}
<nav class="border-b border-stone-200/60 bg-[#f5f5f0] py-3">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-2 text-sm text-stone-500">
            <a href="{{ route('home') }}" class="hover:text-stone-800 transition-colors">{{ __('Home') }}</a>
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <a href="{{ route('products.index') }}" class="hover:text-stone-800 transition-colors">{{ __('Shop') }}</a>
            <svg class="h-4 w-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
            <span class="text-stone-800 font-medium truncate">{{ $product->name_en }}</span>
        </div>
    </div>
</nav>

{{-- Main product section: image left, details right --}}
<section class="bg-[#f5f5f0] py-12 lg:py-16">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-2 lg:gap-14">
            {{-- Product image with optional badge --}}
            <div class="relative">
                <div class="overflow-hidden rounded-2xl bg-white shadow-[0_4px_24px_rgba(90,90,64,0.08)]">
                    @if($product->getFirstMediaUrl('product_images', 'large'))
                        <img src="{{ $product->getFirstMediaUrl('product_images', 'large') }}" alt="{{ $product->name_en }}" class="aspect-square w-full object-cover" />
                    @else
                        <div class="aspect-square w-full flex items-center justify-center bg-gradient-to-br from-stone-100 to-stone-200/80 text-stone-400">
                            <svg class="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    @endif
                </div>
                @if($product->is_highlight || $product->is_trending || $product->is_featured)
                    <div class="lumiere-product-badge absolute bottom-4 right-4 rounded-full px-4 py-2 text-sm font-medium text-white shadow-lg" style="background: linear-gradient(135deg, #b8956e 0%, #C5A059 50%, #a08050 100%);">
                        {{ $product->is_highlight ? __('Handcrafted Daily') : ($product->is_trending ? __('Trending') : __('Featured')) }}
                    </div>
                @endif
            </div>

            {{-- Product details --}}
            <div class="flex flex-col">
                @if($product->category)
                    <p class="text-xs font-medium uppercase tracking-widest text-stone-400 mb-2">{{ $product->category->name_en }}</p>
                @endif
                <h1 class="heading-display text-3xl sm:text-4xl lg:text-5xl text-stone-900 tracking-tight mb-3">{{ $product->name_en }}</h1>
                <p class="text-xl text-stone-800 font-medium mb-6">{{ $symbol }}{{ number_format($product->price, 2) }}</p>

                @if($product->short_description)
                    <p class="text-stone-600 leading-relaxed mb-6">{{ $product->short_description }}</p>
                @endif

                @if($product->description_en)
                    <div class="prose prose-stone max-w-none text-stone-600 text-base leading-relaxed mb-6">
                        {!! nl2br(e($product->description_en)) !!}
                    </div>
                @endif

                {{-- Key ingredients: pill tags --}}
                @if(count($ingredientList) > 0)
                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-stone-200/80 text-stone-500">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            </span>
                            <span class="text-sm font-semibold text-stone-800">{{ __('Key Ingredients') }}</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($ingredientList as $ing)
                                <span class="lumiere-ingredient-tag inline-flex items-center rounded-full border border-stone-300 bg-white/80 px-4 py-2 text-sm text-stone-700">{{ $ing }}</span>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Product note (allergens / dietary info from settings) --}}
                @if(trim(settings('product_note') ?? '') !== '')
                <div class="lumiere-allergens-box rounded-xl border border-stone-200 bg-stone-100/80 px-4 py-3 mb-8 flex gap-3">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-stone-300/80 text-stone-600 text-xs font-bold">i</span>
                    <p class="text-sm text-stone-600 leading-relaxed">
                        {{ settings('product_note') }}
                    </p>
                </div>
                @endif

                {{-- Quantity + Add to Bag --}}
                <div class="mt-auto flex flex-wrap items-center gap-4">
                    <div class="lumiere-qty flex items-center rounded-xl border border-stone-300 bg-white overflow-hidden">
                        <button type="button" id="qty-minus" class="flex h-12 w-12 items-center justify-center text-stone-600 hover:bg-stone-100 transition-colors" aria-label="{{ __('Decrease quantity') }}">−</button>
                        <input type="number" id="product-qty" name="quantity" value="1" min="1" max="10" class="h-12 w-14 border-0 border-x border-stone-200 bg-transparent text-center text-stone-900 font-medium focus:ring-0 focus:outline-none" readonly />
                        <button type="button" id="qty-plus" class="flex h-12 w-12 items-center justify-center text-stone-600 hover:bg-stone-100 transition-colors" aria-label="{{ __('Increase quantity') }}">+</button>
                    </div>
                    <a href="{{ route('order.place', $product) }}" id="add-to-bag-link" class="lumiere-add-to-bag inline-flex flex-1 min-w-[200px] items-center justify-center gap-2 rounded-xl px-8 py-4 text-base font-semibold text-white shadow-md transition-all duration-200 hover:shadow-lg hover:brightness-105" style="background: linear-gradient(135deg, #5A5A40 0%, #4a4a35 100%);">
                        <svg class="h-5 w-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span id="add-to-bag-text">{{ __('Add to Bag') }} – {{ $symbol }}{{ number_format($product->price, 2) }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- You might also love --}}
@if($related->isNotEmpty())
<section class="border-t border-stone-200/60 bg-[#ebebe6] py-16 lg:py-20">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <h2 class="heading-display text-2xl sm:text-3xl text-stone-900 mb-10">{{ __('You might also love') }}</h2>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($related->take(3) as $p)
                @include('products._card', ['product' => $p])
            @endforeach
        </div>
        <div class="mt-10 text-center">
            <a href="{{ route('products.index') }}" class="lumiere-btn-viewall inline-flex items-center gap-2 rounded-xl px-6 py-3 text-sm font-semibold text-white shadow-md transition-all hover:shadow-lg">
                {{ __('View All') }}
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </a>
        </div>
    </div>
</section>
@endif

@push('scripts')
<script>
(function() {
    var qtyInput = document.getElementById('product-qty');
    var link = document.getElementById('add-to-bag-link');
    var label = document.getElementById('add-to-bag-text');
    if (!qtyInput || !link || !label) return;
    var price = {{ (float) $product->price }};
    var symbol = @json($symbol);
    function updateQty(delta) {
        var v = parseInt(qtyInput.value, 10) + delta;
        v = Math.max(1, Math.min(10, v));
        qtyInput.value = v;
        var total = (price * v).toFixed(2);
        label.textContent = @json(__('Add to Bag')) + ' – ' + symbol + total;
        link.href = link.href.split('?')[0] + (v > 1 ? '?quantity=' + v : '');
    }
    document.getElementById('qty-minus')?.addEventListener('click', function() { updateQty(-1); });
    document.getElementById('qty-plus')?.addEventListener('click', function() { updateQty(1); });
})();
</script>
@endpush
@endsection
