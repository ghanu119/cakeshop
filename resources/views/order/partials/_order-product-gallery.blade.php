@php
    $compact = $compact ?? true;
    $galleryImages = $product->orderedProductImages()->map(fn ($media) => [
        'large' => $product->productImageUrl($media, 'large'),
        'medium' => $product->productImageUrl($media, 'medium'),
        'thumb' => $product->productImageUrl($media, 'thumb'),
        'alt' => $product->name_en,
    ])->values();

    $galleryLightboxItems = $galleryImages->map(fn ($image) => [
        'src' => $image['large'],
        'title' => $image['alt'],
    ])->values();
@endphp

@if($galleryImages->isNotEmpty())
    <div
        class="js-product-gallery order-product-gallery {{ $compact ? 'order-product-gallery--compact flex w-full flex-col gap-3' : '' }}"
        data-gallery-items='@json($galleryLightboxItems)'
    >
        @if($compact)
            <div class="order-product-gallery__main relative mx-auto w-full max-w-sm overflow-hidden rounded-2xl border border-stone-200 bg-stone-100 shadow-sm aspect-[4/3]">
                @php $primary = $galleryImages->first(); @endphp
                <a
                    href="{{ $primary['large'] }}"
                    class="js-product-gallery-lightbox js-order-gallery-main product-gallery-link block h-full w-full cursor-zoom-in"
                    data-gallery-index="0"
                    data-medium-src="{{ $primary['medium'] }}"
                    aria-label="{{ __('View full image') }}"
                >
                    <img
                        src="{{ $primary['medium'] }}"
                        alt="{{ $primary['alt'] }}"
                        class="js-order-gallery-main-img h-full w-full object-cover"
                    />
                </a>
            </div>
            @if($galleryImages->count() > 1)
                <div class="order-product-gallery__thumbs mx-auto flex w-full max-w-sm justify-center gap-2 overflow-x-auto pb-0.5" role="list" aria-label="{{ __('Product images') }}">
                    @foreach($galleryImages as $index => $image)
                        <a
                            href="{{ $image['large'] }}"
                            class="js-product-gallery-lightbox order-product-gallery__thumb {{ $index === 0 ? 'is-active' : '' }} h-16 w-16 shrink-0 overflow-hidden rounded-xl border-2 border-stone-200"
                            data-gallery-index="{{ $index }}"
                            data-order-gallery-thumb="{{ $index }}"
                            data-medium-src="{{ $image['medium'] }}"
                            role="listitem"
                            aria-label="{{ __('View image :n', ['n' => $index + 1]) }}"
                            @if($index === 0) aria-current="true" @endif
                        >
                            <img src="{{ $image['thumb'] }}" alt="" class="h-full w-full object-cover" />
                        </a>
                    @endforeach
                </div>
            @endif
        @elseif($galleryImages->count() > 1)
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
        @else
            @php $image = $galleryImages->first(); @endphp
            <a
                href="{{ $image['large'] }}"
                class="js-product-gallery-lightbox product-gallery-link block cursor-zoom-in overflow-hidden rounded-2xl"
                data-gallery-index="0"
                aria-label="{{ __('View full image') }}"
            >
                <img src="{{ $image['medium'] }}" alt="{{ $image['alt'] }}" class="product-gallery-img" />
            </a>
        @endif
    </div>
@else
    <div class="w-20 h-20 shrink-0 rounded-2xl overflow-hidden border border-stone-200 bg-stone-100 shadow-sm sm:h-24 sm:w-24">
        <div class="flex h-full w-full items-center justify-center">
            <svg class="h-8 w-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
        </div>
    </div>
@endif
