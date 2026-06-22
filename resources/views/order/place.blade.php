@extends('layouts.app')

@section('content')
@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $hasVariants = $hasVariants ?? false;
    $hasFlavors = $hasFlavors ?? false;
    $variantChoices = $variantChoices ?? collect();
    $initialFlavorId = old('flavor_id', $product->flavors->first()?->id);
    $initialFlavorName = $product->flavors->firstWhere('id', (int) $initialFlavorId)?->name_en ?? '';
    $initialFulfillmentType = old('fulfillment_type', 'takeaway');
@endphp

@include('order.partials._picker-styles')

<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order') }}: {{ $product->name_en }}</h1>
    <x-card class="mb-6">
        <p class="mb-4 text-gray-600">
            <span id="order-unit-price">{{ $symbol }}{{ number_format($product->price, 2) }}</span>
            {{ $hasVariants ? __('per cake') : __('per piece') }}
        </p>
        @if($hasVariants)
            <p class="mb-2 text-sm font-medium text-gray-700">{{ __('Estimated total') }}: <span id="order-estimated-total">{{ $symbol }}{{ number_format($product->price, 2) }}</span></p>
        @endif
        <form method="post" action="{{ route('order.store', $product) }}" class="space-y-4" @if(!$customer) data-guest-checkout @endif>
            @csrf
            <x-form-errors show-system-errors :show-validation-summary="true" />
            @if($hasVariants && $variantChoices->isNotEmpty())
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">{{ __('Weight') }} *</label>
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
                    >
                        @php $initialVariantId = (int) old('product_variant_id', request('product_variant_id', $defaultVariant?->id)); @endphp
                        @foreach($variantChoices as $choice)
                            @php $isSelected = (string) $initialVariantId === (string) $choice['id']; @endphp
                            <button type="button" data-variant-id="{{ $choice['id'] }}" class="rounded-full border border-gray-300 px-3 py-1 text-sm font-medium" aria-pressed="{{ $isSelected ? 'true' : 'false' }}">{{ $choice['label'] }}</button>
                        @endforeach
                    </div>
                    @error('product_variant_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
            @include('order.partials._flavor-picker', [
                'product' => $product,
                'hasFlavors' => $hasFlavors,
            ])
            @if($customer)
                <x-order.contact-fields :customer="$customer" />
            @else
                <livewire:order.contact-verification />
            @endif
            @if(uses_better_buns_checkout())
                @include('order.partials._fulfillment-order-fields', ['initialFulfillmentType' => $initialFulfillmentType])
            @else
                <div>
                    <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Quantity') }} *</label>
                    <x-input type="number" name="quantity" id="quantity" value="{{ old('quantity', request('quantity', 1)) }}" min="1" max="10" class="block w-full" required />
                    @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endif
            <div>
                <label for="delivery_at" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Delivery date and time') }} * ({{ $deliveryRules['timezone'] }})</label>
                @php
                    $minDt = $deliveryRules['after']->setTimezone($deliveryRules['timezone'])->format('Y-m-d\TH:i');
                    $maxDt = $deliveryRules['before']->setTimezone($deliveryRules['timezone'])->format('Y-m-d\TH:i');
                @endphp
                <x-input type="datetime-local" name="delivery_at" id="delivery_at" value="{{ old('delivery_at') }}" min="{{ $minDt }}" max="{{ $maxDt }}" class="block w-full" required />
                @error('delivery_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message_on_cake" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Message on cake') }}</label>
                <x-input type="text" name="message_on_cake" id="message_on_cake" value="{{ old('message_on_cake') }}" maxlength="{{ $messageOnCakeMaxLength }}" class="block w-full" />
                <p class="mt-1 text-xs text-gray-500">{{ __('Maximum :max characters', ['max' => $messageOnCakeMaxLength]) }}</p>
                @error('message_on_cake')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="instructions" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Instructions') }}</label>
                <textarea name="instructions" id="instructions" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('instructions') }}</textarea>
                @error('instructions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-button type="submit" variant="primary" data-submitting-text="{{ __('Processing...') }}">{{ __('Place order') }}</x-button>
        </form>
    </x-card>

    @include('order.partials._place-order-confirm-modal')
</div>
@endsection
