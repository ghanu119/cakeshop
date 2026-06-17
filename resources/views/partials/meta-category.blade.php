@php
    $siteName = settings('site_name') ?: config('app.name');
    $categoryUrl = route('products.category', $category->slug);
    $description = __('Browse our :category collection — :count handcrafted cakes and treats from :site.', [
        'category' => strtolower($category->name_en),
        'count' => $products->total(),
        'site' => $siteName,
    ]);
@endphp
<meta name="description" content="{{ $description }}">
<link rel="canonical" href="{{ $categoryUrl }}">
<meta property="og:type" content="website">
<meta property="og:title" content="{{ $category->name_en }} – {{ $siteName }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $categoryUrl }}">
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="{{ $category->name_en }} – {{ $siteName }}">
<meta name="twitter:description" content="{{ $description }}">
