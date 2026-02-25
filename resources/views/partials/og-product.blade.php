@php
    $img = $product->getFirstMediaUrl('product_images', 'large') ?: $product->getFirstMediaUrl('product_images');
    $imgUrl = $img ? url($img) : null;
    $currency = settings('currency') ?? 'INR';
@endphp
<meta property="og:title" content="{{ $product->meta_title ?: $product->name_en }}" />
<meta property="og:description" content="{{ $product->meta_description ?: Str::limit($product->short_description ?? $product->description_en, 160) }}" />
@if($imgUrl)
<meta property="og:image" content="{{ $imgUrl }}" />
@endif
<meta property="og:url" content="{{ request()->url() }}" />
<meta property="og:type" content="product" />
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="{{ $product->meta_title ?: $product->name_en }}" />
<meta name="twitter:description" content="{{ $product->meta_description ?: Str::limit($product->short_description ?? $product->description_en, 200) }}" />
@if($imgUrl)
<meta name="twitter:image" content="{{ $imgUrl }}" />
@endif
<link rel="canonical" href="{{ request()->url() }}" />
