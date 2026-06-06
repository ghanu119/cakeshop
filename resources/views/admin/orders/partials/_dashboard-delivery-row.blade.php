@php
    $tz = settings('timezone') ?? 'Asia/Kolkata';
    $deliveryAt = $order->delivery_at?->setTimezone($tz);
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
@endphp

<a
    href="{{ route('admin.orders.show', $order) }}"
    class="group grid grid-cols-[auto_1fr] items-center gap-x-3 gap-y-1 border-b border-gray-100 px-4 py-3 transition last:border-b-0 hover:bg-indigo-50/50 sm:grid-cols-[4.5rem_minmax(0,1fr)_auto_auto] sm:gap-x-4"
>
    @if($deliveryAt)
        <div class="hidden sm:block sm:text-center">
            <p class="text-sm font-bold tabular-nums text-gray-900">{{ $deliveryAt->format('h:i') }}</p>
            <p class="text-[10px] font-medium uppercase tracking-wide text-gray-400">{{ $deliveryAt->format('A') }}</p>
        </div>
    @else
        <span class="hidden sm:block"></span>
    @endif

    <div class="min-w-0">
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="font-mono text-xs font-semibold text-gray-900">#{{ $order->order_no }}</span>
            <x-badge :variant="$statusVariant" class="capitalize">{{ $order->order_status }}</x-badge>
        </div>
        <p class="mt-0.5 truncate text-sm font-medium text-gray-900 group-hover:text-indigo-700">{{ $order->displayProductName() }}</p>
        <p class="truncate text-xs text-gray-500">
            {{ $order->guest_name }}
            @if($deliveryAt)
                <span class="sm:hidden"> · {{ $deliveryAt->format('g:i A') }}</span>
            @endif
        </p>
    </div>

    <div class="col-start-2 sm:col-start-auto sm:justify-self-end">
        <x-badge :variant="$paymentVariant">{{ $order->paymentStatusBadgeLabel() }}</x-badge>
    </div>

    <div class="hidden text-indigo-400 sm:block sm:justify-self-end">
        <svg class="h-4 w-4 opacity-0 transition group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    </div>
</a>
