@php
    $days = $order->daysUntilDelivery();
    $label = $order->daysUntilDeliveryLabel();
    $size = $size ?? 'sm';
    $classes = match (true) {
        $days !== null && $days <= 2 => 'bg-amber-100 text-amber-900 ring-amber-300',
        $days !== null && $days <= 5 => 'bg-indigo-100 text-indigo-900 ring-indigo-200',
        default => 'bg-gray-100 text-gray-700 ring-gray-200',
    };
    $sizeClasses = $size === 'lg'
        ? 'px-3 py-1.5 text-sm'
        : 'px-2.5 py-1 text-xs';
@endphp

@if($days !== null)
    <span class="inline-flex shrink-0 items-center rounded-full font-bold ring-1 {{ $classes }} {{ $sizeClasses }}">
        {{ $label }}
    </span>
@endif
