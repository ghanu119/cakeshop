@php
    $product = $order->product;
    $images = $product ? $product->orderedProductImages() : collect();
    $primary = $images->first();
    $thumbUrl = $primary ? $product->productImageUrl($primary, 'thumb') : null;
    $fullUrl = $primary ? $product->productImageUrl($primary, 'large') : null;
    $imageCount = $images->count();
    $lightboxItems = $images->map(fn ($media) => [
        'src' => $product->productImageUrl($media, 'large'),
        'alt' => $order->displayProductName(),
    ])->values();
@endphp

@if($thumbUrl && $fullUrl)
    <div
        class="relative shrink-0"
        @if($imageCount > 1) data-image-lightbox-items='@json($lightboxItems)' @endif
    >
        <button
            type="button"
            data-image-lightbox
            data-gallery-index="0"
            data-full-src="{{ $fullUrl }}"
            data-alt="{{ $order->displayProductName() }}"
            class="cursor-zoom-in overflow-hidden rounded-lg shadow-sm ring-1 ring-gray-900/10 transition hover:ring-indigo-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
        >
            <img src="{{ $thumbUrl }}" alt="{{ $order->displayProductName() }}" class="h-16 w-16 object-cover" />
        </button>
        @if($imageCount > 1)
            <span class="absolute -bottom-1 -right-1 rounded-full bg-indigo-600 px-1.5 py-0.5 text-[10px] font-bold text-white shadow-sm">
                {{ $imageCount }}
            </span>
        @endif
    </div>
@endif
