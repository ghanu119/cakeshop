@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $imgUrl = $product->getFirstMediaUrl('product_images', 'medium') ?: $product->getFirstMediaUrl('product_images', 'large');
    $weightLabels = collect();
    if ($product->relationLoaded('variants')) {
        foreach ($product->variants->where('status', 'active') as $variant) {
            foreach ($variant->selections ?? [] as $sel) {
                if ($sel->value?->label) {
                    $weightLabels->push($sel->value->label);
                }
            }
        }
        $weightLabels = $weightLabels->unique()->values();
    }
    $hasVariants = $weightLabels->isNotEmpty();
    $flavorLabels = collect();
    if ($product->relationLoaded('flavors')) {
        $flavorLabels = $product->flavors->pluck('name_en')->filter()->values();
    }
    $hasFlavors = $flavorLabels->isNotEmpty();
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
    </div>
    <div class="flex flex-col flex-grow p-6 sm:p-8">
        @if($product->category)
            <p class="text-xs font-bold text-amber-500 uppercase tracking-wider mb-2">{{ $product->category->name_en }}</p>
        @endif
        <h3 class="text-xl font-bold text-stone-900 mb-3 group-hover:text-amber-600 transition-colors line-clamp-1">{{ $product->name_en }}</h3>
        @if($hasVariants)
            <div class="mb-3 flex flex-wrap gap-1">
                @foreach($weightLabels->take(5) as $label)
                    <span class="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-[10px] font-bold text-amber-800 border border-amber-200">{{ $label }}</span>
                @endforeach
            </div>
        @endif
        @if($hasFlavors)
            <div class="mb-3 flex flex-wrap gap-1">
                @foreach($flavorLabels->take(5) as $label)
                    <span class="inline-flex rounded-full bg-rose-50 px-2 py-0.5 text-[10px] font-bold text-rose-800 border border-rose-200">{{ $label }}</span>
                @endforeach
                @if($flavorLabels->count() > 5)
                    <span class="inline-flex rounded-full bg-stone-100 px-2 py-0.5 text-[10px] font-bold text-stone-600 border border-stone-200">+{{ $flavorLabels->count() - 5 }}</span>
                @endif
            </div>
        @endif
        @if($product->short_description)
            <p class="text-sm text-stone-500 mb-6 line-clamp-2 leading-relaxed flex-grow">{{ $product->short_description }}</p>
        @else
            <div class="mb-6 flex-grow"></div>
        @endif
        <div class="mt-auto flex items-end justify-between border-t border-stone-100 pt-5">
            @php
                $formattedPrice = number_format($product->price, 2);
                [$wholePrice, $decimalPrice] = explode('.', $formattedPrice);
            @endphp
            <div class="leading-none">
                @if($hasVariants)<p class="text-[10px] font-bold uppercase text-stone-500 mb-1">{{ __('From') }}</p>@endif
                <p class="inline-flex items-end whitespace-nowrap text-2xl font-extrabold tracking-tight text-stone-900">
                    {{ $symbol }}{{ $wholePrice }}<span class="mb-[2px] text-sm font-semibold text-stone-600">.{{ $decimalPrice }}</span>
                </p>
            </div>
            <div class="flex items-center self-end text-amber-600 font-bold transition-colors group-hover:text-amber-700">
                {{ __('View') }}
                <svg class="ml-1 h-5 w-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </div>
    </div>
</a>
