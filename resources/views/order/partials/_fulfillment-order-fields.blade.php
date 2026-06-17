@php
    $initialFulfillmentType = $initialFulfillmentType ?? old('fulfillment_type', 'takeaway');
    $deliveryNotice = settings('checkout_delivery_notice') ?: __('We deliver only within Rajkot. Enter your pincode to check availability.');
    $takeawayNotice = settings('checkout_takeaway_notice') ?: __('Pickup is only available at our store:');
    $takeawayAddress = settings('checkout_takeaway_address') ?: settings('address');
@endphp

<input type="hidden" name="quantity" id="quantity" value="1" />

<div class="col-span-full md:col-span-2">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 items-start" data-fulfillment-layout>
        {{-- Left: order type + fulfillment-specific details --}}
        <div class="min-w-0 space-y-4 {{ $initialFulfillmentType === 'takeaway' ? 'md:col-span-2' : '' }}" data-fulfillment-left-column>
            <div>
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
                class="{{ $initialFulfillmentType === 'takeaway' ? '' : 'hidden' }}"
                data-takeaway-notice-panel
            >
                <div class="rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3 text-sm text-amber-950">
                    <p class="font-semibold">{{ $takeawayNotice }}</p>
                    @if($takeawayAddress)
                        <p class="mt-1 whitespace-pre-wrap leading-relaxed text-amber-900">{{ $takeawayAddress }}</p>
                    @endif
                </div>
            </div>

            <div
                class="{{ $initialFulfillmentType === 'delivery' ? '' : 'hidden' }}"
                data-delivery-details-panel
                data-delivery-address-panel
                data-delivery-panel
                data-pincode-check-url="{{ route('order.pincode.check') }}"
            >
                <div class="rounded-xl border border-teal-200 bg-teal-50/80 px-4 py-3 text-sm text-teal-950">
                    <p class="font-medium">{{ $deliveryNotice }}</p>
                </div>

                <div class="mt-4">
                    <label for="delivery_pincode" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Delivery pincode') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input
                            type="tel"
                            name="delivery_pincode"
                            id="delivery_pincode"
                            value="{{ old('delivery_pincode') }}"
                            inputmode="numeric"
                            maxlength="6"
                            autocomplete="postal-code"
                            data-delivery-pincode-input
                            placeholder="360001"
                            class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 pr-10 text-stone-900 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm"
                            {{ $initialFulfillmentType === 'delivery' ? '' : 'disabled' }}
                        />
                        <span class="pointer-events-none absolute inset-y-0 right-3 hidden items-center" data-pincode-spinner>
                            <svg class="h-5 w-5 animate-spin text-teal-600" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                        </span>
                    </div>
                    <div class="mt-2 hidden" data-pincode-status-wrap>
                        <div class="flex items-start gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium" data-pincode-status-box>
                            <span class="mt-0.5 shrink-0" data-pincode-status-icon aria-hidden="true"></span>
                            <p class="leading-snug" data-pincode-status role="status" aria-live="polite"></p>
                        </div>
                    </div>
                    @error('delivery_pincode')<p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>@enderror
                </div>

                <button
                    type="button"
                    class="mt-3 hidden text-sm font-semibold text-teal-700 underline hover:text-teal-900"
                    data-switch-to-takeaway
                >{{ __('Switch to Take away instead') }}</button>
            </div>
        </div>

        {{-- Right: delivery address (paired with order type on md+) --}}
        <div
            class="min-w-0 flex flex-col {{ $initialFulfillmentType === 'delivery' ? '' : 'hidden' }}"
            data-delivery-address-column
            data-delivery-panel
        >
            <label for="delivery_address" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Delivery address') }} <span class="text-red-500">*</span></label>
            <textarea
                name="delivery_address"
                id="delivery_address"
                rows="5"
                data-delivery-address-input
                placeholder="{{ __('Street, area, landmark, phone for delivery…') }}"
                class="min-h-[9.5rem] w-full flex-1 rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-teal-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-teal-500/20 transition-all shadow-sm resize-none md:min-h-[11rem]"
                disabled
            >{{ old('delivery_address') }}</textarea>
            @error('delivery_address')<p class="mt-2 text-sm text-red-600 font-medium">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@include('order.partials._picker-styles')

@once
    @push('scripts')
        @vite(['resources/js/order-fulfillment-type.js', 'resources/js/order-pincode-check.js'])
    @endpush
@endonce
