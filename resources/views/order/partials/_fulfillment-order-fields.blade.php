@php
    $initialFulfillmentType = $initialFulfillmentType ?? old('fulfillment_type', 'takeaway');
@endphp

<input type="hidden" name="quantity" id="quantity" value="1" />
<div class="md:col-span-2">
    <label class="mb-2 block text-sm font-bold text-stone-700">{{ __('Order type') }} <span class="text-red-500">*</span></label>
    <input type="hidden" name="fulfillment_type" id="fulfillment_type" value="{{ $initialFulfillmentType }}" />
    <div
        class="flex flex-wrap gap-2 fulfillment-picker"
        data-fulfillment-picker
        role="radiogroup"
        aria-label="{{ __('Order type') }}"
    >
        @foreach(['takeaway' => __('Take away'), 'delivery' => __('Deliver')] as $type => $label)
            @php $isSelected = $initialFulfillmentType === $type; @endphp
            <button
                type="button"
                data-fulfillment-type="{{ $type }}"
                class="rounded-full border border-stone-200 bg-white px-4 py-2 text-sm font-semibold text-stone-800 hover:bg-stone-50"
                aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
            >{{ $label }}</button>
        @endforeach
    </div>
    @error('fulfillment_type')<p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>@enderror
</div>
<div
    class="md:col-span-2 {{ $initialFulfillmentType === 'delivery' ? '' : 'hidden' }}"
    data-delivery-address-panel
>
    <label for="delivery_address" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Delivery address') }} <span class="text-red-500">*</span></label>
    <textarea
        name="delivery_address"
        id="delivery_address"
        rows="3"
        data-delivery-address-input
        placeholder="{{ __('Street, area, landmark, phone for delivery…') }}"
        class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm resize-none"
        {{ $initialFulfillmentType === 'delivery' ? '' : 'disabled' }}
    >{{ old('delivery_address') }}</textarea>
    @error('delivery_address')<p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>@enderror
</div>

@include('order.partials._picker-styles')

@once
    @push('scripts')
        @vite(['resources/js/order-fulfillment-type.js'])
    @endpush
@endonce
