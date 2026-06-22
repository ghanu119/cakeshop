@php
    $isDelivery = $order->isDeliveryFulfillment();
    $timezone = settings('timezone') ?? 'Asia/Kolkata';
    $scheduledLabel = $isDelivery ? __('Delivery scheduled') : __('Pickup scheduled');
    $scheduledAtDisplay = $order->delivery_at
        ? $order->delivery_at->timezone($timezone)->format('M d, Y') . ' · ' . $order->delivery_at->timezone($timezone)->format('h:i A')
        : null;
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="{{ $isDelivery ? 'bg-teal-50 border-teal-100/60' : 'bg-amber-50 border-amber-100/50' }} p-4 rounded-2xl border flex gap-3 items-start">
        <div class="w-8 h-8 rounded-full {{ $isDelivery ? 'bg-teal-100 text-teal-600' : 'bg-amber-100 text-amber-600' }} flex items-center justify-center shrink-0">
            @if($isDelivery)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
            @endif
        </div>
        <div>
            <p class="text-xs font-bold {{ $isDelivery ? 'text-teal-800' : 'text-amber-800' }} uppercase tracking-wider mb-0.5">{{ __('Order type') }}</p>
            <p class="text-sm font-semibold text-stone-800">{{ $order->fulfillmentLabel() }}</p>
        </div>
    </div>

    @if($scheduledAtDisplay)
        <div class="bg-amber-50 p-4 rounded-2xl border border-amber-100/50 flex gap-3 items-start">
            <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-amber-800 uppercase tracking-wider mb-0.5">{{ $scheduledLabel }}</p>
                <p class="text-sm font-medium text-stone-700">{{ $scheduledAtDisplay }}</p>
            </div>
        </div>
    @endif

    @if($isDelivery && $order->delivery_address)
        <div class="sm:col-span-2 bg-teal-50/80 p-4 rounded-2xl border border-teal-100 flex gap-3 items-start">
            <div class="w-8 h-8 rounded-full bg-teal-100 text-teal-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-bold text-teal-800 uppercase tracking-wider mb-0.5">{{ __('Delivery address') }}</p>
                @if($order->delivery_pincode)
                    <p class="text-sm font-semibold text-teal-900 mb-1">{{ $order->delivery_pincode }}</p>
                @endif
                <p class="text-sm font-medium text-stone-800 whitespace-pre-wrap leading-relaxed">{{ $order->delivery_address }}</p>
            </div>
        </div>
    @endif

    @if($order->message_on_cake)
        <div class="bg-stone-50 p-4 rounded-2xl border border-stone-200/60 flex gap-3 items-start">
            <div class="w-8 h-8 rounded-full bg-stone-200 text-stone-600 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
            <div>
                <p class="text-xs font-bold text-stone-500 uppercase tracking-wider mb-0.5">{{ __('Cake message') }}</p>
                <p class="text-sm font-medium text-stone-800 italic">"{{ $order->message_on_cake }}"</p>
            </div>
        </div>
    @endif
</div>
