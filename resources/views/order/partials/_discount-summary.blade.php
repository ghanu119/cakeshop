@props(['order'])

@if($order->hasDiscount())
    @php
        $symbol = (settings('currency') ?? 'INR') === 'INR' ? '₹' : (settings('currency') . ' ');
    @endphp
    @if($order->coupon_code)
        <p class="mb-1 text-sm text-stone-500">
            {{ __('Coupon code') }}:
            <span class="font-mono font-semibold text-stone-700">{{ $order->coupon_code }}</span>
        </p>
    @endif
    <p class="mb-1 text-sm text-gray-600">{{ __('Subtotal') }}: {{ $symbol }}{{ number_format($order->displaySubtotal(), 2) }}</p>
    <p class="mb-1 text-sm text-green-700">
        {{ __('Discount') }}: −{{ $symbol }}{{ number_format((float) $order->discount_amount, 2) }}
        @if($order->coupon_label)
            <span class="text-stone-500">({{ $order->coupon_label }})</span>
        @endif
    </p>
@endif
