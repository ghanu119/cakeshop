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
        class="w-full min-w-0 rounded-lg bg-slate-50 px-3 py-3 sm:flex-1 sm:py-2 lg:py-1.5"
    >
        <div class="space-y-3 text-xs">
            <div class="min-w-0">
                <p class="font-medium text-gray-500">{{ __('Total') }}</p>
                <p class="whitespace-nowrap font-semibold tabular-nums text-gray-900">₹{{ number_format($paymentStats['total_order_amount'], 2) }}</p>
            </div>
            <div class="grid min-w-0 grid-cols-2 gap-3 sm:gap-4">
                <div class="min-w-0">
                    <p class="font-medium text-emerald-600">{{ __('Received') }}</p>
                    <p class="whitespace-nowrap font-semibold tabular-nums text-emerald-700">₹{{ number_format($paymentStats['total_received'], 2) }}</p>
                    <p class="mt-0.5 space-y-0.5 text-gray-400">
                        <span class="block whitespace-nowrap">{{ __('online') }} ₹{{ number_format($paymentStats['online_received'], 2) }}</span>
                        <span class="block whitespace-nowrap">{{ __('cash') }} ₹{{ number_format($paymentStats['cash_received'], 2) }}</span>
                    </p>
                </div>
                <div class="min-w-0">
                    <p class="font-medium text-amber-600">{{ __('Remaining') }}</p>
                    <p class="whitespace-nowrap font-semibold tabular-nums text-amber-700">₹{{ number_format($paymentStats['total_remaining'], 2) }}</p>
                    <p class="mt-0.5 space-y-0.5 text-gray-400">
                        <span class="block whitespace-nowrap">{{ __('pending') }} ₹{{ number_format($paymentStats['pending_amount'], 2) }}</span>
                        <span class="block whitespace-nowrap">{{ __('due') }} ₹{{ number_format($paymentStats['cash_due'], 2) }}</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endif
