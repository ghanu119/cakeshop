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
    $orderShowRoute = $orderShowRoute ?? 'admin.orders.show';
@endphp

<a
    href="{{ route($orderShowRoute, $order) }}"
    class="group block overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:border-orange-200 hover:shadow-md"
>
    <div class="flex gap-3 p-4">
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500">
            <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div class="min-w-0 flex-1">
            <p class="line-clamp-2 text-sm font-bold text-gray-900 group-hover:text-orange-700">{{ $order->displayProductName() }}</p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @if($days !== null && $days > 0)
                    <span class="inline-flex rounded-full bg-rose-100 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide text-rose-700">
                        {{ $order->daysUntilDeliveryLabel() }}
                    </span>
                @endif
                <x-badge :variant="$statusVariant" class="uppercase">{{ $order->order_status }}</x-badge>
            </div>
        </div>
    </div>
    <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 px-4 py-2.5">
        <span class="font-mono text-xs text-gray-500">#{{ $order->order_no }} · {{ __('Qty') }} {{ $order->quantity }}</span>
        <span class="text-xs font-bold uppercase tracking-wide text-orange-600 group-hover:text-orange-700">{{ __('Details') }} →</span>
    </div>
</a>
