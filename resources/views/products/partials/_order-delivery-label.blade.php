<p @class([
    'flex items-center gap-2 text-sm text-gray-700',
    $class ?? '',
])>
    <svg @class([
        'h-4 w-4 shrink-0 text-gray-500',
        $iconClass ?? '',
    ]) fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span>
        {{ __('Earliest Delivery') }}:
        <span @class([
            'font-semibold text-red-600',
            $valueClass ?? '',
        ])>{{ $product->displayEarliestDeliveryLabel() }}</span>
    </span>
</p>
