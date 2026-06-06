@php
    $tz = settings('timezone') ?? 'Asia/Kolkata';
    $submittedAt = $order->payment_made_at?->setTimezone($tz);
@endphp

<a
    href="{{ route('admin.orders.show', $order) }}"
    class="group flex items-center gap-3 rounded-lg border border-amber-100 bg-white px-3 py-2.5 transition duration-200 hover:border-amber-200 hover:bg-amber-50/50 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500 focus-visible:ring-offset-2"
>
    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
    </div>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm font-semibold text-gray-900">
            <span class="font-mono text-xs">#{{ $order->order_no }}</span>
            · ₹ {{ number_format($order->amount, 0) }}
        </p>
        <p class="truncate text-xs text-gray-600">{{ $order->guest_name }}</p>
    </div>
    <div class="shrink-0 text-right">
        @if($submittedAt)
            <p class="text-[10px] text-amber-700">{{ $submittedAt->format('d M') }}</p>
        @endif
        <span class="text-xs font-semibold text-amber-800 group-hover:text-amber-900">{{ __('Review') }}</span>
    </div>
</a>
