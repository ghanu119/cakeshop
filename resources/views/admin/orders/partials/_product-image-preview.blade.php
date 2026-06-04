@php
    $product = $order->product;
    $galleryImages = $product
        ? $product->orderedProductImages()->map(fn ($media) => [
            'full' => $product->productImageUrl($media, 'large'),
            'medium' => $product->productImageUrl($media, 'medium'),
            'thumb' => $product->productImageUrl($media, 'thumb'),
        ])->values()
        : collect();

    $lightboxItems = $galleryImages->map(fn ($image) => [
        'src' => $image['full'],
        'alt' => $order->displayProductName(),
    ])->values();
@endphp

@if($galleryImages->isNotEmpty())
    <div class="border-t border-gray-100 pt-6">
        <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('Product reference') }}</h4>

        <div
            class="admin-product-ref-gallery max-w-md"
            data-image-lightbox-items='@json($lightboxItems)'
        >
            @php $primary = $galleryImages->first(); @endphp
            <button
                type="button"
                data-image-lightbox
                data-gallery-index="0"
                data-full-src="{{ $primary['full'] }}"
                data-alt="{{ $order->displayProductName() }}"
                class="js-admin-ref-main group block w-full cursor-zoom-in overflow-hidden rounded-xl border border-gray-200 bg-gray-50 shadow-sm ring-1 ring-gray-900/5 transition hover:border-indigo-200 hover:ring-indigo-200/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
            >
                <img
                    src="{{ $primary['medium'] }}"
                    alt="{{ $order->displayProductName() }}"
                    class="js-admin-ref-main-img aspect-[4/3] w-full object-cover transition duration-200 group-hover:opacity-95"
                />
            </button>

            @if($galleryImages->count() > 1)
                <div class="mt-3 flex gap-2 overflow-x-auto pb-1">
                    @foreach($galleryImages as $index => $image)
                        <button
                            type="button"
                            data-image-lightbox
                            data-admin-ref-thumb="{{ $index }}"
                            data-gallery-index="{{ $index }}"
                            data-full-src="{{ $image['full'] }}"
                            data-medium-src="{{ $image['medium'] }}"
                            data-alt="{{ $order->displayProductName() }}"
                            class="admin-product-ref-gallery__thumb h-16 w-16 shrink-0 overflow-hidden rounded-lg border-2 {{ $index === 0 ? 'border-indigo-500' : 'border-transparent' }} shadow-sm transition hover:border-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-1"
                        >
                            <img src="{{ $image['thumb'] }}" alt="" class="h-full w-full object-cover" />
                        </button>
                    @endforeach
                </div>
                <p class="mt-2 text-xs text-gray-500">
                    {{ __(':count photos — click any to enlarge', ['count' => $galleryImages->count()]) }}
                </p>
            @else
                <p class="mt-2 text-xs text-gray-500">{{ __('Click to enlarge') }}</p>
            @endif
        </div>
    </div>
@endif
