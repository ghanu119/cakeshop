@php
    $tz = settings('timezone') ?? 'Asia/Kolkata';
    $preparationAt = $order->preparation_at?->setTimezone($tz);
    $prepOverdue = $preparationAt && $preparationAt->isPast() && $order->order_status === 'processing';
    $product = $order->product;
    $images = $product ? $product->orderedProductImages() : collect();
    $primary = $images->first();
    $thumbUrl = $primary ? $product->productImageUrl($primary, 'medium') : null;
    $orderShowRoute = $orderShowRoute ?? 'admin.kitchen.orders.show';
@endphp

<a
    href="{{ route($orderShowRoute, $order) }}"
    class="group flex flex-col overflow-hidden rounded-2xl border bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 {{ $prepOverdue ? 'border-red-200 ring-2 ring-red-100' : 'border-gray-200 hover:border-indigo-200' }}"
>
    <div class="relative aspect-[5/4] w-full overflow-hidden bg-gradient-to-br from-gray-100 to-gray-50">
        @if($thumbUrl)
            <img
                src="{{ $thumbUrl }}"
                alt="{{ $order->displayProductName() }}"
                class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
            />
        @else
            <div class="flex h-full w-full items-center justify-center text-gray-300">
                <svg class="h-14 w-14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
            </div>
        @endif
        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
            <span class="rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-indigo-700 shadow-sm backdrop-blur-sm">
                {{ __('Processing') }}
            </span>
            <span class="rounded-lg bg-black/50 px-2 py-1 font-mono text-[10px] font-medium text-white backdrop-blur-sm">
                #{{ $order->order_no }}
            </span>
        </div>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <h3 class="line-clamp-2 font-semibold leading-snug text-gray-900 transition group-hover:text-indigo-700">
            {{ $order->displayProductName() }}
        </h3>
        <div class="mt-2 flex flex-wrap items-center gap-2 text-xs text-gray-500">
            <span class="inline-flex items-center rounded-md bg-gray-100 px-2 py-0.5 font-medium text-gray-700">
                {{ __('Qty') }} {{ $order->quantity }}
            </span>
            @if($order->hasVariantSnapshot() || $order->hasFlavorSnapshot())
                <span class="truncate">{{ $order->variant_summary ?: $order->displayFlavorName() }}</span>
            @endif
        </div>
    </div>

    @if($preparationAt)
        <div class="flex items-center justify-between gap-3 border-t px-4 py-3 {{ $prepOverdue ? 'border-red-100 bg-red-50' : 'border-indigo-50 bg-indigo-50/60' }}">
            <div class="flex items-center gap-2.5">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $prepOverdue ? 'bg-red-100 text-red-600' : 'bg-indigo-100 text-indigo-600' }}">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-wider {{ $prepOverdue ? 'text-red-500' : 'text-indigo-500' }}">{{ __('Prepare by') }}</p>
                    <p class="text-base font-bold {{ $prepOverdue ? 'text-red-800' : 'text-indigo-900' }}">{{ $preparationAt->format('h:i A') }}</p>
                </div>
            </div>
            @if($prepOverdue)
                <x-badge variant="danger">{{ __('Overdue') }}</x-badge>
            @else
                <svg class="h-5 w-5 shrink-0 text-indigo-400 opacity-0 transition group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            @endif
        </div>
    @endif
</a>
