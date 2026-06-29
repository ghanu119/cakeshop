@extends('layouts.app')

@section('content')
@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    
    // Product Image
    $imgUrl = null;
    if ($order->product && !$order->product->trashed()) {
        $imgUrl = $order->product->getFirstMediaUrl('product_images', 'medium') ?: $order->product->getFirstMediaUrl('product_images', 'large');
    }
@endphp

<div class="min-h-screen bg-stone-50 py-12 lg:py-20 relative">
    <div class="relative z-10 mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        
        {{-- Success Header --}}
        <div class="text-center mb-10">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 text-green-500 mb-6 shadow-sm border-4 border-white">
                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="font-display text-4xl font-extrabold text-stone-900 mb-3">{{ __('Order Confirmed!') }}</h1>
            <p class="text-lg text-stone-600">{{ __('Thank you! Your order has been successfully received.') }}</p>
        </div>

        {{-- Main Receipt Card --}}
        <div class="bg-white rounded-3xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-stone-200 overflow-hidden mb-8">
            
            {{-- Order Reference Highlight --}}
            <div class="bg-stone-50 px-6 sm:px-10 py-6 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mb-1">{{ __('Order Reference') }}</p>
                    <p class="text-lg sm:text-xl font-mono font-bold text-stone-800 break-all">{{ $order->order_no }}</p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ match($order->order_status ?? 'pending') { 'completed' => 'bg-green-100 text-green-700', 'processing' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' } }}">
                        {{ ucfirst($order->order_status ?? 'pending') }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ match(true) {
                            $order->isPaymentVerified() => 'bg-green-100 text-green-700',
                            $order->hasPaymentDetailsSubmitted() => 'bg-blue-100 text-blue-700',
                            default => 'bg-stone-200 text-stone-600',
                        } }}">
                        {{ $order->paymentStatusBadgeLabel() }}
                    </span>
                </div>
            </div>

            {{-- Product Summary --}}
            <div class="p-6 sm:p-10 border-b border-stone-100">
                <h3 class="text-sm font-bold text-stone-400 uppercase tracking-wider mb-6">{{ __('Order Summary') }}</h3>
                
                {{-- Product Row --}}
                <div class="flex items-center gap-5 mb-8">
                    {{-- Thumbnail --}}
                    <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl overflow-hidden shadow-sm shrink-0 bg-stone-100 border border-stone-200">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $order->product?->name_en }}" class="w-full h-full object-cover" />
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="h-8 w-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                        @endif
                    </div>
                    
                    {{-- Details --}}
                    <div class="flex-1">
                        <h4 class="text-lg sm:text-xl font-bold font-display text-stone-900 leading-tight mb-1">{{ $order->displayProductName() }}</h4>
                        @include('order.partials._order-options', [
                            'order' => $order,
                            'weightClass' => 'text-amber-700 text-sm font-semibold mb-1',
                            'flavorClass' => 'text-rose-700 text-sm font-semibold mb-1',
                        ])
                        <p class="text-stone-500 text-sm font-medium">{{ __('Quantity') }}: {{ $order->quantity }}</p>
                        @if($order->unit_price !== null)
                            <p class="text-stone-500 text-sm">{{ __('Unit price') }}: {{ $symbol }}{{ number_format($order->displayUnitPrice(), 2) }}</p>
                        @endif
                        @include('order.partials._discount-summary', ['order' => $order])
                    </div>
                    
                    {{-- Price --}}
                    <div class="text-right">
                        <p class="text-xl sm:text-2xl font-black text-stone-900">{{ $symbol }}{{ number_format($order->amount, 2) }}</p>
                    </div>
                </div>

                @include('order.partials._order-fulfillment-summary', ['order' => $order])
            </div>
            
            @include('order.partials._order-confirm-payment', ['order' => $order])
        </div>

        @include('order.partials._order-footer-nav')

    </div>
</div>
@endsection