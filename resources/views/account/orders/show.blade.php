@extends('layouts.app')

@section('content')
@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
@endphp

<div class="min-h-screen bg-stone-50 py-12 lg:py-16">
    <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
        <header class="mb-8">
            <a href="{{ route('account.orders.index') }}" class="text-sm font-medium text-stone-600 hover:text-stone-900 hover:underline">{{ __('← My orders') }}</a>
            <h1 class="mt-2 font-display text-3xl font-extrabold tracking-tight text-stone-900 sm:text-4xl">{{ __('Order details') }}</h1>
            <p class="mt-1 text-stone-600">{{ $order->order_no }}</p>
        </header>

        <div class="overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)]">
            <div class="flex flex-col justify-between gap-4 border-b border-stone-100 bg-stone-50 px-6 py-6 sm:flex-row sm:items-center sm:px-10">
                @include('order.partials._order-reference', ['order' => $order, 'variant' => 'header'])
                <div class="flex shrink-0 flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider
                        {{ match($order->order_status ?? 'pending') { 'completed' => 'bg-green-100 text-green-700', 'processing' => 'bg-blue-100 text-blue-700', 'cancelled' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-700' } }}">
                        {{ ucfirst($order->order_status ?? 'pending') }}
                    </span>
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-bold uppercase tracking-wider
                        {{ match(true) {
                            $order->isPaymentVerified() => 'bg-green-100 text-green-700',
                            $order->hasPaymentDetailsSubmitted() => 'bg-blue-100 text-blue-700',
                            default => 'bg-stone-200 text-stone-600',
                        } }}">
                        {{ $order->paymentStatusBadgeLabel() }}
                    </span>
                </div>
            </div>

            <div class="border-b border-stone-100 p-6 sm:p-10">
                <h2 class="mb-6 text-sm font-bold uppercase tracking-wider text-stone-400">{{ __('Order summary') }}</h2>

                <div class="mb-8 flex flex-col gap-4">
                    @if($order->product)
                        @include('order.partials._order-product-gallery', ['product' => $order->product, 'compact' => true])
                    @endif

                    <div class="min-w-0 w-full">
                        <div class="mb-3 flex items-baseline justify-between gap-4 border-b border-stone-100 pb-3">
                            <h3 class="font-display min-w-0 flex-1 text-lg font-bold leading-snug text-stone-900">
                                @if($order->product && ! $order->product->trashed())
                                    <a href="{{ route('product.show', $order->product->slug) }}" class="transition-colors hover:text-amber-700 hover:underline">{{ $order->displayProductName() }}</a>
                                @else
                                    {{ $order->displayProductName() }}
                                @endif
                            </h3>
                            <p class="shrink-0 text-xl font-black tabular-nums text-stone-900">{{ $symbol }}{{ number_format($order->amount, 2) }}</p>
                        </div>
                        <div class="space-y-1">
                            @include('order.partials._order-options', [
                                'order' => $order,
                                'weightClass' => 'text-sm font-semibold text-amber-700',
                                'flavorClass' => 'text-sm font-semibold text-rose-700',
                            ])
                            <p class="text-sm font-medium text-stone-500">{{ __('Quantity') }}: {{ $order->quantity }}</p>
                            <p class="text-sm text-stone-500">{{ __('Ordered') }}: {{ $order->ordered_at?->format('d M Y H:i') }}</p>
                            @if($order->unit_price !== null)
                                <p class="text-sm text-stone-500">{{ __('Unit price') }}: {{ $symbol }}{{ number_format($order->displayUnitPrice(), 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @include('order.partials._order-fulfillment-summary', ['order' => $order])

                <div class="mt-8 border-t border-stone-100 pt-6">
                    <h2 class="mb-4 text-sm font-bold uppercase tracking-wider text-stone-400">{{ __('Order contact') }}</h2>
                    <dl class="grid grid-cols-1 gap-3 text-sm sm:grid-cols-2">
                        <div><dt class="text-stone-500">{{ __('Name') }}</dt><dd class="font-medium text-stone-900">{{ $order->guest_name }}</dd></div>
                        <div><dt class="text-stone-500">{{ __('Phone') }}</dt><dd class="font-medium text-stone-900">{{ $order->guest_phone }}</dd></div>
                        @if($order->guest_email)
                            <div class="sm:col-span-2"><dt class="text-stone-500">{{ __('Email') }}</dt><dd class="font-medium text-stone-900">{{ $order->guest_email }}</dd></div>
                        @endif
                    </dl>
                    @if($order->hasDistinctContactFromAccount())
                        <p class="mt-3 text-sm text-stone-500">{{ __('Saved to your account · Contact details above are for this order.') }}</p>
                    @endif
                </div>
            </div>

            @include('order.partials._order-confirm-payment', ['order' => $order])
        </div>

        @include('order.partials._order-footer-nav', ['wrapperClass' => 'mt-8'])
    </div>
</div>
@endsection
