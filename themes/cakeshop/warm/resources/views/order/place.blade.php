@extends('layouts.app')

@section('content')
@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $imgUrl = $product->getFirstMediaUrl('product_images', 'medium') ?: $product->getFirstMediaUrl('product_images', 'large');
    $hasVariants = $hasVariants ?? false;
    $hasFlavors = $hasFlavors ?? false;
    $variantChoices = $variantChoices ?? collect();
    $initialVariantId = (int) old('product_variant_id', request('product_variant_id', $defaultVariant?->id));
    $summaryWeightLabel = ($variantChoices->firstWhere('id', $initialVariantId) ?? $variantChoices->first())['label'] ?? '';
    $initialFlavorId = old('flavor_id', $product->flavors->first()?->id);
    $summaryFlavorLabel = $product->flavors->firstWhere('id', (int) $initialFlavorId)?->name_en ?? '';
    $initialFulfillmentType = old('fulfillment_type', 'takeaway');
@endphp

@include('order.partials._picker-styles')

<div class="min-h-screen bg-stone-50 py-12 lg:py-20 relative overflow-hidden">
    <!-- Soft background glow effect -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[1000px] h-[600px] bg-gradient-to-b from-amber-200/30 to-transparent rounded-full blur-3xl opacity-60 pointer-events-none"></div>

    <div class="relative z-10 mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
        {{-- Header Section --}}
        <div class="text-center mb-12">
            <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-amber-100 text-amber-800 text-sm font-bold uppercase tracking-wider mb-4 shadow-sm">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                {{ __('Secure Checkout') }}
            </span>
            <h1 class="font-display text-4xl sm:text-5xl font-extrabold text-stone-900 mb-4">{{ __('Complete Your Order') }}</h1>
            <p class="text-lg text-stone-600 max-w-2xl mx-auto">{{ __('Please provide your details below to finalize your order for the delicious') }} <span class="font-bold text-amber-700">{{ $product->name_en }}</span></p>
        </div>

        <form
            method="post"
            action="{{ route('order.store', $product) }}"
            class="order-place-layout lg:grid lg:grid-cols-12 lg:gap-10 xl:gap-12 items-start"
            data-order-place-confirm
            @if(!$customer) data-guest-checkout @endif
            @if(!empty($hasVariants)) data-has-variants @endif
            @if(!empty($hasFlavors)) data-has-flavors @endif
            @if(uses_better_buns_checkout()) data-show-order-type @endif
        >
            @csrf
            
            {{-- Left Column: Form Fields --}}
            <div class="order-place-main lg:col-span-7 xl:col-span-8 bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-amber-100/50 p-6 sm:p-10 mb-10 lg:mb-0">
                <x-form-errors show-system-errors :show-validation-summary="true" class="mb-8" />

                @if($customer)
                    <x-order.contact-fields :customer="$customer" variant="checkout" />
                @else
                    <livewire:order.contact-verification />
                @endif

                {{-- Order Details Section --}}
                <div class="mt-10 border-t border-stone-100 pt-10 mb-2">
                    <div class="mb-6 flex items-center gap-3 border-b border-stone-100 pb-4">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-sm font-bold text-white shadow-sm">2</span>
                        <h3 class="text-xl font-bold text-stone-900">{{ __('Order Details') }}</h3>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @if(!empty($hasVariants) && $variantChoices->isNotEmpty())
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-stone-700">{{ __('Weight') }} <span class="text-red-500">*</span></label>
                            <input type="hidden" name="product_variant_id" id="product_variant_id" value="{{ old('product_variant_id', request('product_variant_id', $defaultVariant?->id)) }}" />
                            <div
                                class="flex flex-wrap gap-2 variant-picker"
                                data-variant-picker
                                data-choices="{{ $variantChoices->toJson() }}"
                                data-initial-variant-id="{{ old('product_variant_id', request('product_variant_id', $defaultVariant?->id)) }}"
                                data-currency-symbol="{{ $symbol }}"
                                data-hidden-input-target="#product_variant_id"
                                data-unit-price-target="#order-unit-price"
                                data-total-target="#order-estimated-total"
                                data-quantity-target="#quantity"
                                data-weight-label-target="#order-summary-weight"
                                role="radiogroup"
                            >
                                @foreach($variantChoices as $choice)
                                    @php $isSelected = (string) $initialVariantId === (string) $choice['id']; @endphp
                                    <button type="button" data-variant-id="{{ $choice['id'] }}" class="rounded-full border border-amber-300 bg-white px-4 py-2 text-sm font-semibold text-stone-800 hover:bg-amber-50" aria-pressed="{{ $isSelected ? 'true' : 'false' }}">{{ $choice['label'] }}</button>
                                @endforeach
                            </div>
                            @error('product_variant_id')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                        @endif
                        @include('order.partials._flavor-picker', [
                            'product' => $product,
                            'hasFlavors' => $hasFlavors,
                            'wrapperClass' => 'md:col-span-2',
                            'labelClass' => 'mb-2 block text-sm font-bold text-stone-700',
                            'pickerClass' => 'flex flex-wrap gap-2',
                            'buttonClass' => 'rounded-full border border-rose-200 bg-white px-4 py-2 text-sm font-semibold text-rose-900 hover:bg-rose-50',
                            'errorClass' => 'mt-2 text-sm text-red-600 font-medium',
                            'flavorLabelTarget' => '#order-summary-flavor',
                        ])
                        @include('order.partials._fulfillment-order-fields', ['initialFulfillmentType' => $initialFulfillmentType])
                        @include('order.partials._delivery-datetime-field', [
                            'product' => $product,
                            'deliveryRules' => $deliveryRules,
                            'deliveryBounds' => $deliveryBounds,
                            'suggestedDeliveryAt' => $suggestedDeliveryAt ?? '',
                            'labelClass' => 'mb-2 block text-sm font-bold text-stone-700',
                            'inputClass' => 'w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm',
                            'labelLabel' => __('Delivery Date & Time'),
                            'showTimezone' => true,
                        ])
                        <div class="md:col-span-2">
                            <label for="message_on_cake" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Message on Cake') }} <span class="text-stone-400 font-medium">({{ __('Optional') }})</span></label>
                            <input type="text" name="message_on_cake" id="message_on_cake" value="{{ old('message_on_cake') }}" placeholder="{{ __('e.g. Happy Birthday John!') }}" maxlength="{{ $messageOnCakeMaxLength }}" class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm" />
                            <p class="mt-2 text-xs text-stone-500">{{ __('Maximum :max characters', ['max' => $messageOnCakeMaxLength]) }}</p>
                            @error('message_on_cake')<p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>{{ $message }}</p>@enderror
                        </div>
                        <div class="md:col-span-2">
                            <label for="instructions" class="mb-2 block text-sm font-bold text-stone-700">{{ __('Special Instructions') }} <span class="text-stone-400 font-medium">({{ __('Optional') }})</span></label>
                            <textarea name="instructions" id="instructions" rows="3" placeholder="{{ __('Any allergies, delivery details, etc.') }}" class="w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm resize-none">{{ old('instructions') }}</textarea>
                            @error('instructions')<p class="mt-2 text-sm text-red-600 font-medium flex items-center gap-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>{{ $message }}</p>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Order Summary --}}
            <div class="order-place-sidebar lg:col-span-5 xl:col-span-4 sticky top-8">
                <div class="bg-white rounded-3xl shadow-[0_20px_40px_rgb(217,119,6,0.1)] border border-amber-100/80 overflow-hidden flex flex-col">
                    {{-- Summary Header with Thumbnail --}}
                    <div class="p-6 sm:p-8 bg-amber-50/50 border-b border-amber-100/80">
                        <h3 class="text-xl font-bold text-stone-900 mb-6">{{ __('Order Summary') }}</h3>
                        <div class="flex items-center gap-5">
                            {{-- Thumbnail Image --}}
                            <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-2xl overflow-hidden shadow-sm shrink-0 bg-white border border-stone-100">
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="{{ $product->name_en }}" class="w-full h-full object-cover" />
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-amber-50 to-orange-50">
                                        <svg class="h-10 w-10 text-amber-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                            </div>
                            {{-- Product Info --}}
                            <div class="flex-1">
                                @if($product->category)
                                    <span class="inline-block text-[10px] sm:text-xs font-bold text-amber-600 uppercase tracking-wider mb-1">{{ $product->category->name_en }}</span>
                                @endif
                                <h4 class="text-lg sm:text-xl font-bold font-display text-stone-900 leading-tight line-clamp-3">{{ $product->name_en }}</h4>
                                @if(filled($product->sku))
                                    <p class="mt-1 text-xs text-stone-500">{{ __('SKU') }}: <span class="font-mono">{{ $product->sku }}</span></p>
                                @endif
                            </div>
                        </div>

                        @if(!empty($hasVariants) && $variantChoices->isNotEmpty())
                        <div class="mt-8 pt-6 border-t border-amber-200/70" aria-live="polite" aria-atomic="true">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-amber-600 mb-2.5">{{ __('Selected weight') }}</p>
                            <div class="flex items-center gap-3 rounded-xl border border-amber-300/80 bg-amber-50/80 px-3.5 py-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-amber-500 text-white shrink-0" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V11"/></svg>
                                </span>
                                <span id="order-summary-weight" class="text-base font-bold text-amber-900">{{ $summaryWeightLabel }}</span>
                            </div>
                        </div>
                        @endif
                        @if(!empty($hasFlavors) && $product->flavors->isNotEmpty())
                        <div class="mt-6 pt-6 border-t border-rose-200/70" aria-live="polite" aria-atomic="true">
                            <p class="text-[10px] sm:text-xs font-bold uppercase tracking-wider text-rose-600 mb-2.5">{{ __('Selected flavor') }}</p>
                            <div class="flex items-center gap-3 rounded-xl border border-rose-200/80 bg-rose-50/80 px-3.5 py-2.5">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-rose-500 text-white shrink-0" aria-hidden="true">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                                </span>
                                <span id="order-summary-flavor" class="text-base font-bold text-rose-900">{{ $summaryFlavorLabel }}</span>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Summary Details --}}
                    <div class="p-6 sm:p-8 bg-white">
                        <div class="flex items-center justify-between border-b border-stone-100 pb-6 mb-6">
                            <span class="text-stone-500 font-medium">{{ !empty($hasVariants) ? __('Price per cake') : __('Price per piece') }}</span>
                            <span class="text-xl font-bold text-stone-900" id="order-unit-price">{{ $symbol }}{{ number_format($product->price, 2) }}</span>
                        </div>
                        @if(!empty($hasVariants))
                        <div class="flex items-center justify-between border-b border-stone-100 pb-6 mb-6">
                            <span class="text-stone-500 font-medium">{{ __('Estimated total') }}</span>
                            <span class="text-xl font-bold text-amber-700" id="order-estimated-total">{{ $symbol }}{{ number_format($product->price, 2) }}</span>
                        </div>
                        @endif

                        <div class="space-y-4 mb-8">
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm text-stone-600 leading-relaxed">{{ __('100% Freshly baked to order') }}</p>
                            </div>
                            <div class="flex items-start gap-3">
                                <div class="w-6 h-6 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <p class="text-sm text-stone-600 leading-relaxed">{{ __('Premium quality ingredients') }}</p>
                            </div>
                        </div>

                        @include('order.partials._coupon-picker', compact('product', 'universalCoupons', 'autoApplyPreview', 'defaultCouponId', 'defaultCouponCode', 'customer'))

                        {{-- Action Buttons --}}
                        <div class="flex flex-col gap-4">
                            <button type="submit" class="w-full px-8 py-4 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-white font-bold text-lg shadow-[0_8px_20px_rgb(217,119,6,0.25)] hover:shadow-[0_12px_25px_rgb(217,119,6,0.35)] hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                                {{ __('Place Order') }}
                                <svg class="w-5 h-5 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                            </button>
                            <a href="{{ route('product.show', $product->slug) }}" class="w-full px-8 py-3.5 rounded-full border-2 border-stone-200 text-stone-600 font-bold hover:border-stone-300 hover:text-stone-900 transition-colors text-center">
                                {{ __('Cancel') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>

        @include('order.partials._place-order-confirm-modal')
    </div>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/order-coupon-summary.js'])
@endpush

@push('styles')
<style>
    /* Fallback layout for desktop when missing lg:grid-cols-12 utilities */
    @media (min-width: 1024px) {
        .order-place-layout {
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            gap: 2.5rem;
            align-items: start;
        }

        .order-place-main,
        .order-place-sidebar {
            min-width: 0;
        }
    }
</style>
@endpush