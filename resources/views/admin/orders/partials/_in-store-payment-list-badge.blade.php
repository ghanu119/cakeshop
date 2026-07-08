@php
    $verified = $order->isPaymentVerified();
    $balanceDue = $order->balanceDue();
    $hasDue = $order->hasOutstandingBalance();
    $badgeLabel = match (true) {
        $verified && $hasDue => __('Due - ₹:amount', ['amount' => number_format($balanceDue, 2)]),
        $verified => __('Paid'),
        $order->isPartiallyPaid() => __('Due - ₹:amount', ['amount' => number_format($balanceDue, 2)]),
        default => __('Pay later'),
    };
    $badgeVariant = $verified ? 'success' : 'warning';
@endphp

<x-badge :variant="$badgeVariant" class="inline-flex items-center gap-1 whitespace-nowrap">
    @if($verified)
        <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
    @endif
    <span>{{ $badgeLabel }}</span>
</x-badge>
