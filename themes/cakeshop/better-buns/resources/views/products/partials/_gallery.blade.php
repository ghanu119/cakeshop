@php
    $galleryImages = $product->orderedProductImages()->map(fn ($media) => [
        'large' => $product->productImageUrl($media, 'large'),
        'medium' => $product->productImageUrl($media, 'medium'),
        'alt' => $product->name_en,
    ])->values();

    $galleryLightboxItems = $galleryImages->map(fn ($image) => [
        'src' => $image['large'],
        'title' => $image['alt'],
    ])->values();
@endphp

<div class="space-y-4">
    @if($galleryImages->isNotEmpty())
        @if($galleryImages->count() > 1)
            <div
                class="js-product-gallery product-gallery-shell overflow-hidden rounded-2xl bg-stone-100 shadow-lg"
                data-gallery
                data-gallery-items='@json($galleryLightboxItems)'
            >
                <div class="js-product-gallery-main product-gallery-main">
                    @foreach($galleryImages as $index => $image)
                        <div class="product-gallery-slide">
                            <a
                                href="{{ $image['large'] }}"
                                class="js-product-gallery-lightbox product-gallery-link cursor-zoom-in"
                                data-gallery-index="{{ $index }}"
                                aria-label="{{ __('View full image :n', ['n' => $index + 1]) }}"
                            >
                                <img
                                    src="{{ $image['medium'] }}"
                                    alt="{{ $image['alt'] }}"
                                    class="product-gallery-img"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                />
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="js-product-gallery-thumbs flex gap-2 overflow-x-auto pb-1">
                @foreach($galleryImages as $index => $image)
                    <button
                        type="button"
                        class="product-gallery-thumb aspect-square w-16 shrink-0 overflow-hidden rounded-lg border-2 border-transparent sm:w-20 {{ $index === 0 ? 'is-active border-amber-500' : '' }}"
                        data-slide="{{ $index }}"
                        aria-label="{{ __('View image :n', ['n' => $index + 1]) }}"
                    >
                        <img src="{{ $image['medium'] }}" alt="" class="h-full w-full object-cover" />
                    </button>
                @endforeach
            </div>
        @else
            @php $image = $galleryImages->first(); @endphp
            <div
                class="js-product-gallery product-gallery-shell overflow-hidden rounded-2xl bg-stone-100 shadow-lg"
                data-gallery-items='@json($galleryLightboxItems)'
            >
                <a
                    href="{{ $image['large'] }}"
                    class="js-product-gallery-lightbox product-gallery-link cursor-zoom-in"
                    data-gallery-index="0"
                    aria-label="{{ __('View full image') }}"
                >
                    <img
                        src="{{ $image['medium'] }}"
                        alt="{{ $image['alt'] }}"
                        class="product-gallery-img"
                    />
                </a>
            </div>
        @endif
    @else
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-amber-50 to-orange-50 shadow-lg">
            <div class="flex aspect-square w-full items-center justify-center text-amber-200">
                <svg class="h-24 w-24" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
        </div>
    @endif

    @if($product->is_highlight || $product->is_trending || $product->is_featured)
        <div class="flex flex-wrap gap-2">
            @if($product->is_highlight)
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white shadow-md" style="background-color: #f59e0b;">{{ __('Highlight') }}</span>
            @endif
            @if($product->is_trending)
                <span class="inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold text-white shadow-md" style="background-color: #ef4444;">{{ __('Trending') }}</span>
            @endif
            @if($product->is_featured)
                <span class="inline-flex items-center rounded-full bg-stone-900 px-4 py-2 text-sm font-semibold text-white shadow-md">{{ __('Featured') }}</span>
            @endif
        </div>
    @endif
</div>
