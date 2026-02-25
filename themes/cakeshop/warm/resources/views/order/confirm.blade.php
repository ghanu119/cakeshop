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
                    <p class="text-lg sm:text-xl font-mono font-bold text-stone-800 break-all">{{ $order->uuid }}</p>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ match($order->order_status ?? 'pending') { 'completed' => 'bg-green-100 text-green-700', 'processing' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' } }}">
                        {{ ucfirst($order->order_status ?? 'pending') }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider 
                        {{ $order->isPaymentVerified() ? 'bg-green-100 text-green-700' : 'bg-stone-200 text-stone-600' }}">
                        {{ $order->isPaymentVerified() ? __('Paid') : __('Payment Pending') }}
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
                        @if($order->product && !$order->product->trashed())
                            <h4 class="text-lg sm:text-xl font-bold font-display text-stone-900 leading-tight mb-1">{{ $order->product->name_en }}</h4>
                        @else
                            <h4 class="text-lg sm:text-xl font-bold font-display text-stone-500 leading-tight mb-1">{{ $order->product?->name_en ?? __('Unknown Product') }}</h4>
                            <span class="inline-block px-2 py-0.5 rounded bg-stone-100 text-stone-500 text-[10px] font-bold uppercase mb-1">{{ __('Unavailable') }}</span>
                        @endif
                        <p class="text-stone-500 text-sm font-medium">{{ __('Quantity') }}: {{ $order->quantity }}</p>
                    </div>
                    
                    {{-- Price --}}
                    <div class="text-right">
                        <p class="text-xl sm:text-2xl font-black text-stone-900">{{ $symbol }}{{ number_format($order->amount, 2) }}</p>
                    </div>
                </div>

                {{-- Delivery & Message --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @if($order->delivery_at)
                        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100/50 flex gap-3 items-start">
                            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-0.5">{{ __('Delivery Scheduled') }}</p>
                                <p class="text-sm font-medium text-stone-700">{{ $order->delivery_at->format('M d, Y') }} <br/> {{ $order->delivery_at->format('h:i A') }}</p>
                            </div>
                        </div>
                    @endif
                    
                    @if($order->message_on_cake)
                        <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200/60 flex gap-3 items-start">
                            <div class="w-8 h-8 rounded-full bg-stone-200 text-stone-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-stone-500 uppercase tracking-wider mb-0.5">{{ __('Cake Message') }}</p>
                                <p class="text-sm font-medium text-stone-800 italic">"{{ $order->message_on_cake }}"</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
            
            {{-- Payment Section --}}
            @if(!$order->isPaymentVerified())
                <div class="p-6 sm:p-10">
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-stone-900">{{ __('Payment Required') }}</h3>
                            <p class="text-sm text-stone-500">{{ __('Please complete your payment to begin processing.') }}</p>
                        </div>
                    </div>

                    <div class="bg-stone-50 rounded-2xl p-6 sm:p-8 mb-8 border border-stone-200">
                        {{-- QR Code (if available) --}}
                        @php $paymentQrUrl = \App\Models\SiteSetting::first()?->getFirstMediaUrl('payment_qr'); @endphp
                        @if($paymentQrUrl)
                            <div class="flex flex-col items-center mb-6 border-b border-stone-200 pb-6">
                                <div class="bg-white p-3 rounded-2xl shadow-sm border border-stone-200 mb-3">
                                    <img src="{{ $paymentQrUrl }}" alt="{{ __('Payment QR code') }}" class="w-40 h-40 object-contain" />
                                </div>
                                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Scan to Pay') }}</span>
                            </div>
                        @endif

                        {{-- Instructions text --}}
                        <div class="prose prose-sm max-w-none text-stone-600 leading-relaxed text-center sm:text-left">
                            {!! nl2br(e(settings('payment_instructions') ?? __('Please pay via UPI to the number shown, or transfer to our bank account. Mention your order ID in the payment note.'))) !!}
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <p class="text-sm font-medium text-stone-600 mb-4">
                            {!! nl2br(e(settings('payment_submit_instructions') ?? __('Already paid? Share your transaction details and screenshot with us.'))) !!}
                        </p>
                        <a href="{{ route('order.submit-payment', ['uuid' => $order->uuid]) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 font-bold text-white shadow-[0_8px_20px_rgb(217,119,6,0.25)] hover:shadow-[0_12px_25px_rgb(217,119,6,0.35)] hover:-translate-y-0.5 transition-all text-lg gap-2">
                            {{ __('Submit Payment Details') }}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                </div>
            @else
                <div class="p-6 sm:p-10 bg-green-50/50">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 border border-green-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-green-900 mb-2">{{ __('Payment Verified') }}</h2>
                            <p class="text-green-700 leading-relaxed">{{ __('Your payment has been successfully verified. We are processing your order and will get in touch if needed. Thank you!') }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Footer Links --}}
        <div class="text-center flex flex-col sm:flex-row items-center justify-center gap-6 mt-8">
            <a href="{{ route('order.history') }}" class="text-stone-500 hover:text-stone-900 font-semibold transition-colors flex items-center gap-2 bg-white px-5 py-2.5 rounded-full shadow-sm border border-stone-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                {{ __('Look up orders by phone') }}
            </a>
            <a href="{{ route('home') }}" class="text-stone-500 hover:text-stone-900 font-semibold transition-colors flex items-center gap-2 bg-white px-5 py-2.5 rounded-full shadow-sm border border-stone-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ __('Back to home') }}
            </a>
        </div>

    </div>
</div>
@endsection