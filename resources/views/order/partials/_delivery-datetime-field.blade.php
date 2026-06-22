@php
    $bounds = $deliveryBounds ?? app(\App\Services\OrderService::class)->deliveryAtBoundsForInput($deliveryRules);
    $labelClass = $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700';
    $inputClass = $inputClass ?? 'block w-full';
    $showTimezone = $showTimezone ?? false;
@endphp
<div>
    <label for="delivery_at" class="{{ $labelClass }}">
        {{ $labelLabel ?? __('Delivery date and time') }} *
        @if($showTimezone)
            <span class="font-normal text-gray-500">({{ $bounds['timezone'] }})</span>
        @endif
    </label>
    <input
        type="datetime-local"
        name="delivery_at"
        id="delivery_at"
        value="{{ old('delivery_at', $suggestedDeliveryAt ?? '') }}"
        min="{{ $bounds['min'] }}"
        max="{{ $bounds['max'] }}"
        step="60"
        class="{{ $inputClass }}"
        required
        data-delivery-datetime
        data-min-message="{{ __('Please choose a time on or after :time.', ['time' => $bounds['min_display']]) }}"
    />
    <p class="mt-1 text-xs leading-tight text-gray-500">
        {{ __('Earliest available') }}:
        <span class="whitespace-nowrap font-medium text-gray-600">{{ $bounds['min_display'] }} ({{ $bounds['timezone'] }})</span>
        @if(filled($product->earliest_delivery_label))
            <span class="whitespace-nowrap text-gray-400"> · {{ $product->earliest_delivery_label }}</span>
        @endif
    </p>
    @error('delivery_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

@once
    @push('scripts')
        @vite(['resources/js/order-delivery-datetime.js'])
    @endpush
@endonce
