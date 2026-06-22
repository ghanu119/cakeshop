@if(filled($product->sku))
    <p class="text-sm text-gray-500">{{ __('SKU') }}: <span class="font-mono text-gray-700">{{ $product->sku }}</span></p>
@endif
