@php
    $paymentStats = $paymentStats ?? [
        'order_count' => 0,
        'total_order_amount' => 0.0,
        'online_received' => 0.0,
        'cash_received' => 0.0,
        'total_received' => 0.0,
        'pending_amount' => 0.0,
        'cash_due' => 0.0,
        'total_remaining' => 0.0,
    ];
@endphp

@if($paymentStats['order_count'] > 0)
    <div
        id="admin-orders-payment-stats"
        class="flex min-h-[38px] min-w-0 flex-1 items-center rounded-lg bg-slate-50 px-3 py-1.5"
    >
        <p class="w-full text-xs leading-relaxed text-gray-600 xl:whitespace-nowrap">
            <span class="font-medium text-gray-500">{{ __('Total') }}</span>
            <span class="font-semibold tabular-nums text-gray-900">₹{{ number_format($paymentStats['total_order_amount'], 2) }}</span>
            <span class="mx-2 hidden text-gray-300 sm:inline">·</span>
            <span class="mt-1 block sm:mt-0 sm:inline">
                <span class="font-medium text-emerald-600">{{ __('Received') }}</span>
                <span class="font-semibold tabular-nums text-emerald-700">₹{{ number_format($paymentStats['total_received'], 2) }}</span>
                <span class="text-gray-400">
                    ({{ __('online') }} ₹{{ number_format($paymentStats['online_received'], 2) }},
                    {{ __('cash') }} ₹{{ number_format($paymentStats['cash_received'], 2) }})
                </span>
            </span>
            <span class="mx-2 hidden text-gray-300 sm:inline">·</span>
            <span class="mt-1 block sm:mt-0 sm:inline">
                <span class="font-medium text-amber-600">{{ __('Remaining') }}</span>
                <span class="font-semibold tabular-nums text-amber-700">₹{{ number_format($paymentStats['total_remaining'], 2) }}</span>
                <span class="text-gray-400">
                    ({{ __('pending') }} ₹{{ number_format($paymentStats['pending_amount'], 2) }},
                    {{ __('due') }} ₹{{ number_format($paymentStats['cash_due'], 2) }})
                </span>
            </span>
        </p>
    </div>
@endif
