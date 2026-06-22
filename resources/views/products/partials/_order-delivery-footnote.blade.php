@php
    $slot = $product->earliestDeliverySlot();
@endphp
<p @class([
    'mt-1 text-[11px] leading-snug text-gray-500',
    $class ?? '',
])>
    {{ __('Nearest slot available') }}:
    <span class="font-medium text-gray-600">{{ $slot['datetime'] }}</span>
    <span class="text-gray-400">({{ $slot['timezone'] }})</span>
</p>
