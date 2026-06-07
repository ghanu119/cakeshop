@php
    $justPlaced = session('order_placed');
    $variant = $variant ?? 'header';
@endphp

<div
    @if($justPlaced) data-order-placed @endif
    @class([
        'min-w-0',
        'rounded-xl p-3 -m-3 ring-2 ring-amber-400 animate-pulse' => $justPlaced && $variant === 'header',
        'mb-4 rounded-xl border border-amber-200 bg-amber-50/80 p-4 ring-2 ring-amber-400 animate-pulse' => $justPlaced && $variant === 'card',
    ])
>
    @if($variant === 'card')
        <p class="mb-1 text-sm font-medium text-gray-700">{{ __('Order reference') }}</p>
    @else
        <p class="mb-1 text-xs font-bold uppercase tracking-wider text-stone-400">{{ __('Order Reference') }}</p>
    @endif

    <div class="flex flex-col items-start gap-2">
        <p @class([
            'break-all font-mono font-bold text-stone-800',
            'text-lg sm:text-xl' => $variant === 'header',
            'text-lg text-gray-900' => $variant === 'card',
        ])>{{ $order->order_no }}</p>
        @include('order.partials._copy-button', [
            'text' => $order->order_no,
            'label' => __('Copy order number'),
            'toast' => __('Order number copied!'),
        ])
    </div>

    @if($justPlaced)
        <p @class([
            'mt-3 text-sm leading-relaxed text-amber-900',
            'font-medium' => $variant === 'header',
        ])>
            {{ __('Copy and save this order number to track status and view order details later.') }}
            <a href="{{ route('order.history') }}" class="font-semibold underline underline-offset-2 hover:text-amber-950">{{ __('Look up orders by phone') }}</a>
        </p>
    @elseif($variant === 'card')
        <p class="mt-2 text-sm text-gray-600">{{ __('Keep this reference for payment and tracking.') }}</p>
    @endif
</div>
