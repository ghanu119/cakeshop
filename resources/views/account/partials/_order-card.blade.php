@props([
    'order',
    'showAmount' => false,
])

<a
    href="{{ route('account.orders.show', $order) }}"
    class="mb-4 flex flex-col gap-4 rounded-xl border border-stone-200 bg-white p-6 shadow-sm transition hover:border-amber-300 hover:shadow-md sm:flex-row sm:items-center sm:justify-between sm:p-8"
>
    <div class="min-w-0 flex-1">
        <p class="font-medium text-stone-900">{{ $order->displayProductName() }}</p>
        <p class="mt-1 text-sm text-stone-500">
            {{ $order->order_no }}
            @if($order->hasDistinctContactFromAccount())
                · {{ __('For') }} {{ $order->guest_name }}
            @endif
            @if($showAmount)
                · ₹ {{ number_format($order->amount, 2) }}
            @else
                · {{ $order->ordered_at?->format('d M Y') }}
            @endif
        </p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-badge :variant="$order->payment_status === 'verified' ? 'success' : 'warning'">
                {{ $order->payment_status === 'verified' ? __('Payment verified') : __('Payment pending') }}
            </x-badge>
            @if($showAmount)
                <x-badge variant="default">{{ ucfirst($order->order_status ?? 'pending') }}</x-badge>
            @endif
        </div>
    </div>
    <span class="inline-flex shrink-0 items-center gap-2 text-sm font-semibold text-amber-700">
        {{ __('View details') }}
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
    </span>
</a>
