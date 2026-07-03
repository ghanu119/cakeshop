@extends('layouts.app')

@section('content')
@if(active_theme() === 'better-buns')
    @include('order.partials._order-confirm-better-buns', [
        'order' => $order,
        'paymentCheckoutConfig' => $paymentCheckoutConfig ?? ['enabled' => false, 'gateway' => null, 'key_id' => null],
    ])
@else
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order confirmed') }}</h1>

    <x-card class="mb-6">
        @include('order.partials._order-reference', ['order' => $order, 'variant' => 'card'])
        <p class="mb-2">
            {{ __('Product') }}:
            @if($order->product && ! $order->product->trashed())
                <a href="{{ route('product.show', $order->product->slug) }}" class="font-medium text-gray-900 underline hover:no-underline">{{ $order->displayProductName() }}</a>
            @else
                <span class="font-medium text-gray-900">{{ $order->displayProductName() }}</span>
                @if($order->product?->trashed())
                    <span class="text-sm text-gray-500">({{ __('no longer available') }})</span>
                @endif
            @endif
        </p>
        @include('order.partials._order-options', [
            'order' => $order,
            'weightClass' => 'mb-2 text-amber-800 font-medium',
            'flavorClass' => 'mb-2 text-rose-800 font-medium',
        ])
        <p class="mb-2">{{ __('Quantity') }}: {{ $order->quantity }}</p>
        <p class="mb-2">{{ __('Unit price') }}: ₹ {{ number_format($order->displayUnitPrice(), 2) }}</p>
        @include('order.partials._discount-summary', ['order' => $order])
        <p class="mb-4">{{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}</p>

        <div class="mb-4 border-t border-stone-200 pt-4">
            <h2 class="mb-3 text-sm font-bold uppercase tracking-wider text-stone-500">{{ __('Order contact') }}</h2>
            <p class="mb-1">{{ __('Name') }}: {{ $order->guest_name }}</p>
            <p class="mb-1">{{ __('Phone') }}: {{ $order->guest_phone }}</p>
            @if($order->guest_email)
                <p class="mb-1">{{ __('Email') }}: {{ $order->guest_email }}</p>
            @endif
            @auth('customer')
                @if($order->hasDistinctContactFromAccount())
                    <p class="mt-2 text-sm text-stone-500">{{ __('Saved to your account · Contact details above are for this order.') }}</p>
                @endif
            @endauth
        </div>

        <div class="mb-4 border-t border-stone-200 pt-4">
            @include('order.partials._order-fulfillment-summary', ['order' => $order])
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-4">
            <span class="text-sm font-medium text-stone-500">{{ __('Order status') }}:</span>
            <x-badge :variant="match($order->order_status ?? 'pending') { 'completed' => 'success', 'processing' => 'primary', 'cancelled' => 'danger', default => 'warning' }">{{ ucfirst($order->order_status ?? 'pending') }}</x-badge>
            <span class="text-sm font-medium text-stone-500">{{ __('Payment') }}:</span>
            <x-badge :variant="match(true) {
                $order->isPaymentVerified() => 'success',
                $order->hasPaymentDetailsSubmitted() => 'primary',
                default => 'warning',
            }">{{ $order->paymentStatusBadgeLabel() }}</x-badge>
        </div>

        <div class="border-t border-stone-200">
            @include('order.partials._order-confirm-payment', ['order' => $order])
        </div>
    </x-card>

    @include('order.partials._order-footer-nav')
</div>
@endif
@endsection
