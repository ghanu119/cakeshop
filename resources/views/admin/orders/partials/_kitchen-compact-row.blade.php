@php
    $tz = settings('timezone') ?? 'Asia/Kolkata';
    $preparationAt = $order->preparation_at?->setTimezone($tz);
    $prepOverdue = $preparationAt && $preparationAt->isPast() && $order->order_status === 'processing';
    $product = $order->product;
    $images = $product ? $product->orderedProductImages() : collect();
    $primary = $images->first();
    $thumbUrl = $primary ? $product->productImageUrl($primary, 'thumb') : null;
    $orderShowRoute = $orderShowRoute ?? 'admin.orders.show';
@endphp

<a
    href="{{ route($orderShowRoute, $order) }}"
    class="group flex items-center gap-3 px-4 py-3 transition hover:bg-orange-50/60 {{ $prepOverdue ? 'bg-red-50/40' : '' }}"
>
    <div class="relative size-11 shrink-0 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-black/5">
        @if($thumbUrl)
            <img src="{{ $thumbUrl }}" alt="" class="h-full w-full object-cover" />
        @else
            <div class="flex h-full w-full items-center justify-center text-gray-300">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
            </div>
        @endif
    </div>

    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-gray-900 group-hover:text-orange-700">{{ $order->displayProductName() }}</p>
        <p class="text-xs text-gray-500">#{{ $order->order_no }} · {{ __('Qty') }} {{ $order->quantity }}</p>
    </div>

    @if($preparationAt)
        <div class="shrink-0 text-right">
            <p class="text-[10px] font-bold uppercase tracking-wide {{ $prepOverdue ? 'text-red-500' : 'text-orange-500' }}">{{ __('Prep by') }}</p>
            <p class="text-sm font-bold tabular-nums {{ $prepOverdue ? 'text-red-800' : 'text-gray-900' }}">{{ $preparationAt->format('g:i A') }}</p>
        </div>
    @endif
</a>
