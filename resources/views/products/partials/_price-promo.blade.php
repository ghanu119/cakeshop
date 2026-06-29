@props([
    'promo' => null,
    'symbol' => '₹',
    'size' => 'card',
    'originalPrice' => 0,
])

@if($promo)
    @if($size === 'card')
        <div class="flex flex-col">
            <div class="mb-0.5 flex flex-wrap items-center gap-1.5">
                <span class="price-promo-original text-sm text-stone-500">{{ $symbol }}{{ number_format($promo['original_price'], 2) }}</span>
                <span class="price-promo-badge price-promo-badge--card">{{ $promo['badge_text'] }}</span>
            </div>
            <p class="text-2xl font-display font-bold text-stone-900">{{ $symbol }}{{ number_format($promo['discounted_price'], 2) }}</p>
        </div>
    @else
        <div>
            <div class="flex flex-wrap items-center gap-2" id="product-original-price-row">
                <span class="price-promo-original text-sm text-stone-500" id="product-original-price">{{ $symbol }}{{ number_format($promo['original_price'], 2) }}</span>
                <span class="price-promo-badge" id="product-promo-badge">{{ $promo['badge_text'] }}</span>
            </div>
            <p class="mt-1 text-4xl font-bold text-gray-900" id="product-unit-price">{{ $symbol }}{{ number_format($promo['discounted_price'], 2) }}</p>
        </div>
    @endif
@else
    @if($size === 'card')
        <p class="text-2xl font-display font-bold text-stone-900">{{ $symbol }}{{ number_format($originalPrice ?? 0, 2) }}</p>
    @else
        <p class="mt-1 text-4xl font-bold text-gray-900" id="product-unit-price">{{ $symbol }}{{ number_format($originalPrice ?? 0, 2) }}</p>
    @endif
@endif
