@php
    $product = $order->product;
    $images = $product ? $product->orderedProductImages() : collect();
    $primary = $images->first();
    $thumbUrl = $primary ? $product->productImageUrl($primary, 'thumb') : null;
    $statusVariant = match($order->order_status) {
        'pending' => 'warning',
        'processing' => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
        default => 'default',
    };
    $days = $order->daysUntilDelivery();
    $borderAccent = match (true) {
        $days !== null && $days <= 2 => 'border-l-4 border-l-amber-400',
        $days !== null && $days <= 5 => 'border-l-4 border-l-indigo-400',
        default => 'border-l-4 border-l-transparent',
    };
    $orderShowRoute = $orderShowRoute ?? 'admin.kitchen.orders.upcoming.show';
@endphp

<a
    href="{{ route($orderShowRoute, $order) }}"
    class="group block rounded-xl border border-gray-100 bg-white p-3 shadow-sm transition duration-200 hover:border-indigo-200 hover:bg-indigo-50/30 hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 {{ $borderAccent }}"
>
    <div class="flex gap-3">
        <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-900/5">
            @if($thumbUrl)
                <img src="{{ $thumbUrl }}" alt="{{ $order->displayProductName() }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
            @else
                <div class="flex h-full w-full items-center justify-center text-gray-300">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <p class="line-clamp-2 text-sm font-semibold leading-snug text-gray-900 group-hover:text-indigo-800">
                {{ $order->displayProductName() }}
            </p>
            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                @include('kitchen.orders.partials._days-until-delivery-badge', ['order' => $order])
                <x-badge :variant="$statusVariant" class="capitalize">{{ $order->order_status }}</x-badge>
            </div>
        </div>
    </div>

    <div class="mt-2.5 flex items-center justify-between gap-2 border-t border-gray-100 pt-2.5">
        <div class="min-w-0 text-xs text-gray-500">
            <span class="font-mono">#{{ $order->order_no }}</span>
            <span class="mx-1 text-gray-300">·</span>
            <span>{{ __('Qty') }} {{ $order->quantity }}</span>
        </div>
        <span class="shrink-0 text-xs font-medium text-indigo-600 group-hover:text-indigo-800">
            {{ __('Details') }} →
        </span>
    </div>
</a>
