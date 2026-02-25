@php
    $img = $product->getFirstMediaUrl('product_images', 'large') ?: $product->getFirstMediaUrl('product_images');
    $imgUrl = $img ? url($img) : null;
    $currency = settings('currency') ?? 'INR';
@endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "Product",
    "name": {{ json_encode($product->name_en) }},
    "description": {{ json_encode($product->meta_description ?: $product->short_description ?? $product->description_en) }},
    @if($imgUrl)
    "image": {{ json_encode($imgUrl) }},
    @endif
    "offers": {
        "@@type": "Offer",
        "price": {{ json_encode((string) $product->price) }},
        "priceCurrency": {{ json_encode($currency) }}
    }
}
</script>
