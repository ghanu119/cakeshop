@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
@endphp

<div class="relative min-h-screen bg-stone-50 py-12 lg:py-20">
    <div class="relative z-10 mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">

        <div class="mb-10 text-center">
            <div class="mb-6 inline-flex h-20 w-20 items-center justify-center rounded-full border-4 border-white bg-green-100 text-green-500 shadow-sm">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </div>
            <h1 class="font-display mb-3 text-4xl font-extrabold text-stone-900">{{ __('Order Confirmed!') }}</h1>
            <p class="text-lg text-stone-600">{{ __('Thank you! Your order has been successfully received.') }}</p>
        </div>

        <div class="mb-8 overflow-hidden rounded-3xl border border-stone-200 bg-white shadow-[0_8px_30px_rgb(0,0,0,0.04)]">

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
                <h3 class="mb-6 text-sm font-bold uppercase tracking-wider text-stone-400">{{ __('Order Summary') }}</h3>

                <div class="order-summary-item mb-8 flex flex-col gap-4">
                    @if($order->product)
                        @include('order.partials._order-product-gallery', ['product' => $order->product, 'compact' => true])
                    @else
                        <div class="mx-auto h-32 w-32 shrink-0 overflow-hidden rounded-2xl border border-stone-200 bg-stone-100 shadow-sm">
                            <div class="flex h-full w-full items-center justify-center">
                                <svg class="h-8 w-8 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    @endif

                    <div class="min-w-0 w-full">
                        <div class="order-summary-item__header mb-3 flex items-baseline justify-between gap-4 border-b border-stone-100 pb-3">
                            <h4 class="font-display min-w-0 flex-1 text-lg font-bold leading-snug text-stone-900">
                                @if($order->product && ! $order->product->trashed())
                                    <a href="{{ route('products.show', $order->product->slug) }}" class="transition-colors hover:text-amber-700 hover:underline">{{ $order->displayProductName() }}</a>
                                @else
                                    <span>{{ $order->displayProductName() }}</span>
                                    @if($order->product?->trashed())
                                        <span class="text-sm font-normal text-stone-500">({{ __('no longer available') }})</span>
                                    @endif
                                @endif
                            </h4>
                            <p class="shrink-0 text-xl font-black tabular-nums text-stone-900">{{ $symbol }}{{ number_format($order->amount, 2) }}</p>
                        </div>
                        <div class="space-y-1">
                            @include('order.partials._order-options', [
                                'order' => $order,
                                'weightClass' => 'text-sm font-semibold text-amber-700',
                                'flavorClass' => 'text-sm font-semibold text-rose-700',
                            ])
                            <p class="text-sm font-medium text-stone-500">{{ __('Quantity') }}: {{ $order->quantity }}</p>
                            @if($order->unit_price !== null)
                                <p class="text-sm text-stone-500">{{ __('Unit price') }}: {{ $symbol }}{{ number_format($order->displayUnitPrice(), 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                @include('order.partials._order-fulfillment-summary', ['order' => $order])
            </div>

            @include('order.partials._order-confirm-payment', ['order' => $order])
        </div>

        <div class="mt-8 flex flex-col items-center justify-center gap-6 text-center sm:flex-row">
            <a href="{{ route('order.history') }}" class="flex items-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 font-semibold text-stone-500 shadow-sm transition-colors hover:text-stone-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                {{ __('Look up orders by phone') }}
            </a>
            <a href="{{ route('home') }}" class="flex items-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 font-semibold text-stone-500 shadow-sm transition-colors hover:text-stone-900">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ __('Back to home') }}
            </a>
        </div>

    </div>
</div>
