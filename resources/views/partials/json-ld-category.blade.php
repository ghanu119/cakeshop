@php
    $siteName = settings('site_name') ?: config('app.name');
    $categoryUrl = route('products.category', $category->slug);
    $description = __('Browse our :category collection — :count handcrafted cakes and treats from :site.', [
        'category' => strtolower($category->name_en),
        'count' => $products->total(),
        'site' => $siteName,
    ]);
    $items = collect($products->items())->values()->map(function ($product, int $index) use ($products) {
        return [
            '@type' => 'ListItem',
            'position' => (($products->currentPage() - 1) * $products->perPage()) + $index + 1,
            'url' => route('product.show', $product->slug),
            'name' => $product->name_en,
        ];
    });
@endphp
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "CollectionPage",
    "name": {{ json_encode($category->name_en) }},
    "description": {{ json_encode($description) }},
    "url": {{ json_encode($categoryUrl) }},
    "isPartOf": {
        "@@type": "WebSite",
        "name": {{ json_encode($siteName) }},
        "url": {{ json_encode(url('/')) }}
    }
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "BreadcrumbList",
    "itemListElement": [
        {
            "@@type": "ListItem",
            "position": 1,
            "name": {{ json_encode(__('Home')) }},
            "item": {{ json_encode(route('home')) }}
        },
        {
            "@@type": "ListItem",
            "position": 2,
            "name": {{ json_encode(__('Products')) }},
            "item": {{ json_encode(route('products.index')) }}
        },
        {
            "@@type": "ListItem",
            "position": 3,
            "name": {{ json_encode($category->name_en) }},
            "item": {{ json_encode($categoryUrl) }}
        }
    ]
}
</script>
@if($items->isNotEmpty())
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@@type": "ItemList",
    "name": {{ json_encode($category->name_en) }},
    "numberOfItems": {{ $products->total() }},
    "itemListElement": {!! json_encode($items, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
}
</script>
@endif
