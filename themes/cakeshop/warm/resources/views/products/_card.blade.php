@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $imgUrl = $product->getFirstMediaUrl('product_images', 'medium') ?: $product->getFirstMediaUrl('product_images', 'large');
@endphp
<a href="{{ route('products.show', $product->slug) }}" class="group flex flex-col bg-white rounded-3xl shadow-[0_4px_20px_rgb(0,0,0,0.03)] hover:shadow-[0_20px_40px_rgb(217,119,6,0.12)] border border-amber-100/50 hover:border-amber-200 transition-all duration-500 hover:-translate-y-1.5 overflow-hidden h-full">
    <div class="relative overflow-hidden aspect-[4/3] bg-stone-50">
        @if($imgUrl)
            <img src="{{ $imgUrl }}" alt="{{ $product->name_en }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" />
        @else
            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-50 to-orange-50">
                <svg class="h-16 w-16 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        @endif
        
        {{-- Badges --}}
        @if($product->is_highlight || $product->is_trending || $product->is_featured)
            <div class="absolute top-4 right-4 z-10 flex flex-col gap-2">
                @if($product->is_highlight)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-white text-xs font-black uppercase tracking-wider shadow-md" style="background-color: #f59e0b;">{{ __('Highlight') }}</span>
                @elseif($product->is_trending)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-white text-xs font-black uppercase tracking-wider shadow-md" style="background-color: #ef4444;">{{ __('Trending') }}</span>
                @elseif($product->is_featured)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-white text-xs font-black uppercase tracking-wider shadow-md" style="background-color: #1c1917;">{{ __('Featured') }}</span>
                @endif
            </div>
        @endif
        
        <div class="absolute inset-0 bg-gradient-to-t from-stone-900/40 via-stone-900/0 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
    </div>
    
    <div class="flex flex-col flex-grow p-6 sm:p-8">
        @if($product->category)
            <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-2">{{ $product->category->name_en }}</p>
        @endif
        
        <h3 class="text-xl font-bold text-stone-900 mb-3 group-hover:text-amber-600 transition-colors line-clamp-1">{{ $product->name_en }}</h3>
        
        @if($product->short_description)
            <p class="text-sm text-stone-500 mb-6 line-clamp-2 leading-relaxed flex-grow">{{ $product->short_description }}</p>
        @else
            <div class="mb-6 flex-grow"></div>
        @endif
        
        <div class="flex items-center justify-between pt-5 border-t border-stone-100 mt-auto">
            <div>
                <p class="text-2xl font-black text-stone-900 tracking-tight">{{ $symbol }}{{ number_format($product->price, 2) }}</p>
            </div>
            
            <div class="flex items-center text-amber-600 font-bold group-hover:text-amber-700 transition-colors">
                {{ __('View') }}
                <svg class="ml-1 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </div>
        </div>
    </div>
</a>