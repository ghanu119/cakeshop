@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order confirmed') }}</h1>

    <x-card class="mb-6">
        <p class="mb-2 text-lg font-medium text-gray-900">{{ __('Order reference') }}: <span class="font-mono">{{ $order->uuid }}</span></p>
        <p class="mb-4 text-gray-600">{{ __('Keep this reference for payment and tracking.') }}</p>
        <p class="mb-2">
            {{ __('Product') }}:
            @if($order->product)
                @if($order->product->trashed())
                    <span class="text-gray-600">{{ $order->product->name_en }}</span>
                    <span class="text-sm text-gray-500">({{ __('no longer available') }})</span>
                @else
                    <a href="{{ route('products.show', $order->product->slug) }}" class="font-medium text-gray-900 underline hover:no-underline">{{ $order->product->name_en }}</a>
                @endif
            @else
                <span class="text-gray-500">{{ __('Product no longer available') }}</span>
            @endif
        </p>
        <p class="mb-2">{{ __('Quantity') }}: {{ $order->quantity }}</p>
        <p class="mb-4">{{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}</p>

        <div class="mb-4 flex flex-wrap items-center gap-3 border-t border-stone-200 pt-4">
            <span class="text-sm font-medium text-stone-500">{{ __('Order status') }}:</span>
            <x-badge :variant="match($order->order_status ?? 'pending') { 'completed' => 'success', 'processing' => 'primary', 'cancelled' => 'danger', default => 'warning' }">{{ ucfirst($order->order_status ?? 'pending') }}</x-badge>
            <span class="text-sm font-medium text-stone-500">{{ __('Payment') }}:</span>
            <x-badge :variant="$order->isPaymentVerified() ? 'success' : 'warning'">{{ $order->isPaymentVerified() ? __('Verified') : __('Pending') }}</x-badge>
        </div>

        @if(!$order->isPaymentVerified())
        <div class="border-t border-stone-200 pt-4">
            <h2 class="mb-2 text-xl font-semibold text-stone-900">{{ __('How to pay') }}</h2>
            @php $paymentQrUrl = \App\Models\SiteSetting::first()?->getFirstMediaUrl('payment_qr'); @endphp
            @if($paymentQrUrl)
                <div class="mb-4">
                    <img src="{{ $paymentQrUrl }}" alt="{{ __('Payment QR code') }}" class="h-48 w-48 rounded-lg border border-stone-200 object-contain bg-white p-2" />
                </div>
            @endif
            <div class="prose prose-sm max-w-none text-stone-700">
                {!! nl2br(e(settings('payment_instructions') ?? __('Please contact us for payment details.'))) !!}
            </div>
        </div>

        <div class="mt-4 border-t border-gray-200 pt-4">
            <h2 class="mb-2 text-xl font-semibold text-gray-900">{{ __('After payment') }}</h2>
            <p class="mb-4 text-gray-700">
                {!! nl2br(e(settings('payment_submit_instructions') ?? __('Share your transaction/UPI reference, amount paid, and time of payment. You may upload a screenshot.'))) !!}
            </p>
            <a href="{{ route('order.submit-payment', ['uuid' => $order->uuid]) }}" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Submit payment details') }}
            </a>
        </div>
        @else
        <div class="border-t border-stone-200 pt-4">
            <p class="text-stone-700">{{ __('Your payment has been verified. We are processing your order and will get in touch if needed.') }}</p>
        </div>
        @endif
    </x-card>

    <p class="text-center text-sm text-gray-500">
        <a href="{{ route('order.history') }}" class="text-gray-600 hover:underline">{{ __('Look up orders by phone') }}</a>
        <span class="mx-2">·</span>
        <a href="{{ route('home') }}" class="text-gray-600 hover:underline">{{ __('Back to home') }}</a>
    </p>
</div>
@endsection
