@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $imgUrl = $product->getFirstMediaUrl('product_images', 'medium') ?: $product->getFirstMediaUrl('product_images', 'large');
@endphp
<a href="{{ route('product.show', $product->slug) }}" class="product-card group flex h-full flex-col overflow-hidden rounded-2xl border border-amber-100/60 bg-white shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-amber-200 hover:shadow-[0_12px_28px_rgb(217,119,6,0.12)]">
    <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $product->name_en }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
        @else
            <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-amber-50 to-orange-50">
                <svg class="h-12 w-12 text-amber-200 sm:h-14 sm:w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif

        @if($product->is_highlight || $product->is_trending || $product->is_featured)
            <div class="absolute right-2 top-2 z-10 sm:right-2.5 sm:top-2.5">
                @if($product->is_highlight)
                    <span class="inline-flex rounded-md bg-amber-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">{{ __('Highlight') }}</span>
                @elseif($product->is_trending)
                    <span class="inline-flex rounded-md bg-red-500 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">{{ __('Trending') }}</span>
                @elseif($product->is_featured)
                    <span class="inline-flex rounded-md bg-stone-900 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-white shadow-sm">{{ __('Featured') }}</span>
                @endif
            </div>
        @endif
    </div>

    <div class="flex flex-1 flex-col p-3.5 sm:p-4">
        @if($product->category)
            <p class="mb-1 text-[10px] font-bold uppercase tracking-wider text-amber-600 sm:text-xs">{{ $product->category->name_en }}</p>
        @endif

        <h3 class="mb-1.5 line-clamp-2 text-sm font-bold leading-snug text-stone-900 transition group-hover:text-amber-700 sm:text-base">{{ $product->name_en }}</h3>

        @if($product->short_description)
            <p class="mb-3 line-clamp-2 flex-grow text-xs leading-relaxed text-stone-500 sm:text-sm">{{ $product->short_description }}</p>
        @else
            <div class="mb-3 flex-grow"></div>
        @endif

        <div class="mt-auto flex items-center justify-between gap-2 border-t border-stone-100 pt-3">
            @include('products.partials._price-promo', [
                'promo' => $product->storefront_promo ?? null,
                'symbol' => $symbol,
                'size' => 'card',
                'originalPrice' => $product->price,
            ])
            <span class="inline-flex shrink-0 items-center text-xs font-bold text-amber-600 sm:text-sm">
                {{ __('View') }}
                <svg class="ml-0.5 h-4 w-4 transition group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
            </span>
        </div>
    </div>
</a>
