@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $cardImages = $product->orderedProductImages()
        ->map(fn ($media) => $product->productImageUrl($media, 'medium'))
        ->filter()
        ->values();
    $imgUrl = $cardImages->first();
    $cardImageCount = $cardImages->count();
    $productUrl = route('products.show', $product->slug);
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
    $maxVisibleWeights = 3;
    $visibleWeights = $weightLabels->take($maxVisibleWeights);
    $hasMoreWeights = $weightLabels->count() > $maxVisibleWeights;
    $availableInText = $visibleWeights->implode(', ') . ($hasMoreWeights ? ' ' . __('and more..') : '');
@endphp
<article class="group flex min-w-0 max-w-full flex-col overflow-hidden h-full rounded-3xl border border-amber-100/50 bg-white shadow-[0_4px_20px_rgb(0,0,0,0.03)] transition-all duration-500 hover:-translate-y-1.5 hover:border-amber-200 hover:shadow-[0_20px_40px_rgb(217,119,6,0.12)]">
    <div
        class="product-card-media bg-stone-50"
        data-product-url="{{ $productUrl }}"
    >
        @if($cardImageCount > 1)
            <div class="js-product-card-slider product-card-slider">
                @foreach($cardImages as $index => $cardImgUrl)
                    <div class="product-card-slide">
                        <img
                            src="{{ $cardImgUrl }}"
                            alt="{{ $product->name_en }}"
                            class="product-card-media-img transition-transform duration-700 group-hover:scale-105"
                            loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                        />
                    </div>
                @endforeach
            </div>
            <div
                class="product-card-dots"
                role="tablist"
                aria-label="{{ __('Product images') }}"
            >
                @foreach($cardImages as $index => $cardImgUrl)
                    <button
                        type="button"
                        class="product-card-dot {{ $index === 0 ? 'is-active' : '' }}"
                        data-slide="{{ $index }}"
                        role="tab"
                        aria-label="{{ __('Image :n of :total', ['n' => $index + 1, 'total' => $cardImageCount]) }}"
                        aria-selected="{{ $index === 0 ? 'true' : 'false' }}"
                    ></button>
                @endforeach
            </div>
        @elseif($imgUrl)
            <a href="{{ $productUrl }}" class="product-card-media-cover block" tabindex="-1" aria-hidden="true">
                <img src="{{ $imgUrl }}" alt="{{ $product->name_en }}" class="product-card-media-img transition-transform duration-700 group-hover:scale-105" />
            </a>
        @else
            <a href="{{ $productUrl }}" class="product-card-media-cover flex items-center justify-center bg-gradient-to-br from-amber-50 to-orange-50" tabindex="-1" aria-hidden="true">
                <svg class="h-16 w-16 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </a>
        @endif
        @if($product->is_highlight || $product->is_trending || $product->is_featured)
            <div class="pointer-events-none absolute top-4 right-4 z-30 flex flex-col gap-2">
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
    <a href="{{ $productUrl }}" class="product-card-body flex min-h-0 flex-1 flex-col px-6 pt-4 sm:px-8">
        @if($product->category)
            <div class="mb-3 flex items-center gap-1">
                <svg class="h-3 w-3 shrink-0 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span class="truncate text-[10px] font-medium leading-none text-stone-500">{{ $product->category->name_en }}</span>
            </div>
        @endif
        <h3 class="mb-2 line-clamp-2 text-lg font-bold leading-snug text-stone-900 transition-colors group-hover:text-amber-600 truncate">{{ $product->name_en }}</h3>
        @if($product->short_description)
            <p class="mb-4 line-clamp-2 flex-grow text-sm leading-relaxed text-stone-500">{{ $product->short_description }}</p>
        @else
            <div class="mb-4 flex-grow"></div>
        @endif
        <div class="mb-3 min-h-5">
            @if($hasVariants)
                <p class="text-xs leading-5 text-stone-500 line-clamp-1">
                    <span class="font-semibold text-stone-600">{{ __('Available in:') }}</span>
                    {{ $availableInText }}
                </p>
            @endif
        </div>
        <div class="mt-auto flex items-end justify-between border-t border-stone-100 pt-4">
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
    </a>
</article>
