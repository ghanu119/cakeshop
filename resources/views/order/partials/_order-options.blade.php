@if($order->hasVariantSnapshot())
    <p class="{{ $weightClass ?? 'text-sm text-amber-700' }}">{{ __('Weight') }}: {{ $order->variant_summary }}</p>
@endif
@if($order->hasFlavorSnapshot())
    <p class="{{ $flavorClass ?? 'text-sm text-rose-700' }}">{{ __('Flavor') }}: {{ $order->displayFlavorName() }}</p>
@endif
