@php
    $dotColors = ['bg-orange-400', 'bg-rose-400', 'bg-amber-400', 'bg-violet-400', 'bg-sky-400'];
    $dotColor = $dotColors[$loop->index % count($dotColors)] ?? 'bg-orange-400';
@endphp

<a
    href="{{ route('admin.orders.show', $order) }}"
    class="group flex items-center gap-3 border-b border-gray-100 px-4 py-3.5 transition last:border-b-0 hover:bg-gray-50/80 sm:px-5"
>
    <span class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full {{ $dotColor }} ring-2 ring-white"></span>
    <div class="min-w-0 flex-1">
        <p class="truncate text-sm text-gray-600">
            <span class="font-bold text-gray-900">{{ $order->guest_name }}</span>
            <span class="mx-1 text-gray-300">·</span>
            <span class="font-semibold text-gray-800">{{ $order->displayProductName() }}</span>
        </p>
        <p class="mt-0.5 text-xs text-gray-400">
            {{ $order->ordered_at?->diffForHumans() }}
            <span class="mx-1">·</span>
            <span class="font-mono">#{{ $order->order_no }}</span>
        </p>
    </div>
    <span class="shrink-0 text-sm font-bold tabular-nums text-orange-600 group-hover:text-orange-700">
        ₹{{ number_format($order->amount, 0) }}
    </span>
</a>
