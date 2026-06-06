@php
    $statusVariant = match($order->order_status) {
        'pending' => 'warning',
        'processing' => 'info',
        'completed', 'delivered' => 'success',
        'cancelled' => 'danger',
        default => 'default',
    };
    $paymentVariant = $order->isPaymentVerified() ? 'success' : ($order->hasPaymentDetailsSubmitted() ? 'warning' : 'default');
@endphp

<a
    href="{{ route('admin.orders.show', $order) }}"
    class="group grid grid-cols-[minmax(0,1fr)_auto] items-center gap-x-3 border-b border-gray-100 px-4 py-2.5 transition last:border-b-0 hover:bg-gray-50 sm:grid-cols-[7rem_minmax(0,1fr)_auto_auto]"
>
    <span class="font-mono text-xs font-semibold text-indigo-600 group-hover:text-indigo-800">#{{ $order->order_no }}</span>
    <span class="truncate text-sm text-gray-700">{{ $order->guest_name }}</span>
    <x-badge :variant="$statusVariant" class="capitalize">{{ $order->order_status }}</x-badge>
    <x-badge :variant="$paymentVariant">{{ $order->paymentStatusBadgeLabel() }}</x-badge>
</a>
