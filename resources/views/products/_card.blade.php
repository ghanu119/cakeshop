@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $imgUrl = $product->getFirstMediaUrl('product_images', 'medium') ?: $product->getFirstMediaUrl('product_images', 'large');
@endphp
<a href="{{ route('products.show', $product->slug) }}" class="group block card-modern overflow-hidden p-0">
    <div class="relative overflow-hidden bg-amber-50/50 aspect-square">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $product->name_en }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110" />
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-100 to-orange-100">
                <svg class="h-20 w-20 text-amber-400/70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        @if($product->is_highlight || $product->is_trending || $product->is_featured)
            <div class="absolute top-4 right-4 z-10">
                @if($product->is_highlight)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold shadow-lg">{{ __('Highlight') }}</span>
                @elseif($product->is_trending)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-red-500 to-pink-500 text-white text-xs font-bold shadow-lg">{{ __('Trending') }}</span>
                @elseif($product->is_featured)
                    <span class="inline-flex items-center px-3 py-1 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 text-white text-xs font-bold shadow-lg">{{ __('Featured') }}</span>
                @endif
            </div>
        @endif
        <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
    </div>
    <div class="p-6">
        <h3 class="text-xl font-display font-bold text-stone-900 mb-2 group-hover:text-amber-600 transition-colors line-clamp-1">{{ $product->name_en }}</h3>
        @if($product->short_description)
            <p class="text-sm text-stone-600 mb-4 line-clamp-2 leading-relaxed">{{ $product->short_description }}</p>
        @endif
        <div class="flex items-center justify-between pt-4 border-t border-amber-100/80">
            <div>
                <p class="text-2xl font-display font-bold text-stone-900">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
                @if($product->category)
                    <p class="text-xs text-stone-500 mt-1">{{ $product->category->name_en }}</p>
                @endif
            </div>
            <div class="flex items-center text-amber-600 font-semibold group-hover:text-amber-700 transition-colors">
                {{ __('View') }}
                <svg class="ml-1 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                </svg>
            </div>
        </div>
    </div>
</a>
