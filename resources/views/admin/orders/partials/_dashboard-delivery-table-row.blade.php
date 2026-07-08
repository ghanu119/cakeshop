@php
    $product = $order->product;
    $images = $product ? $product->orderedProductImages() : collect();
    $primary = $images->first();
    $thumbUrl = $primary ? $product->productImageUrl($primary, 'thumb') : null;
    $statusVariant = match($order->order_status) {
        'pending' => 'warning',
        'processing' => 'info',
        'completed', 'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'default',
    };
    $paymentVariant = match(true) {
        $order->isPaymentVerified() => 'success',
        $order->hasPaymentDetailsSubmitted() => 'warning',
        default => 'default',
    };
    $paymentLabel = match(true) {
        $order->isPaymentVerified() => __('Paid'),
        $order->hasPaymentDetailsSubmitted() => __('Awaiting'),
        default => __('Pending'),
    };
    $showVerifiedTick = $order->isPaymentVerified();
@endphp

<tr class="group border-b border-gray-100 transition hover:bg-orange-50/30 last:border-b-0">
    <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
        <a href="{{ route('admin.orders.show', $order) }}" class="font-mono text-xs font-bold text-orange-600 hover:text-orange-700">
            #{{ $order->order_no }}
        </a>
    </td>
    <td class="px-4 py-3.5 sm:px-4">
        <a href="{{ route('admin.orders.show', $order) }}" class="flex items-center gap-3 min-w-0">
            <div class="h-10 w-10 shrink-0 overflow-hidden rounded-lg bg-gray-100 ring-1 ring-gray-900/5">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="" class="h-full w-full object-cover" />
                @else
                    <div class="flex h-full w-full items-center justify-center text-gray-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"/></svg>
                    </div>
                @endif
            </div>
            <span class="truncate text-sm font-semibold text-gray-900 group-hover:text-orange-700">{{ $order->displayProductName() }}</span>
        </a>
    </td>
    <td class="hidden truncate px-4 py-3.5 text-sm text-gray-600 md:table-cell md:px-4">{{ $order->guest_name }}</td>
    <td class="whitespace-nowrap px-4 py-3.5 sm:px-4">
        <x-badge :variant="$statusVariant" class="uppercase tracking-wide">{{ $order->order_status }}</x-badge>
    </td>
    <td class="whitespace-nowrap px-4 py-3.5 sm:px-6">
        @if($order->isInStoreOrder())
            @include('admin.orders.partials._in-store-payment-list-badge', ['order' => $order])
        @else
            <span class="inline-flex items-center gap-1.5">
                @if($showVerifiedTick)
                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </span>
                @else
                    <span class="inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                        <svg class="h-3 w-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                @endif
                <x-badge :variant="$paymentVariant" class="uppercase">{{ $paymentLabel }}</x-badge>
            </span>
        @endif
    </td>
</tr>
