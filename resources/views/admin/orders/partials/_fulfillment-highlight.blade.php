@role('Admin')
    @php
        $isDelivery = $order->isDeliveryFulfillment();
    @endphp
    <div class="rounded-xl border {{ $isDelivery ? 'border-teal-200 bg-gradient-to-br from-teal-50 to-emerald-50/80' : 'border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50/80' }} p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <p class="mb-1 text-sm font-bold uppercase tracking-wider {{ $isDelivery ? 'text-teal-700' : 'text-amber-700' }}">
                    {{ __('Order type') }}
                </p>
                <div class="flex items-center gap-3">
                    <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-sm font-bold {{ $isDelivery ? 'bg-teal-600 text-white' : 'bg-amber-600 text-white' }}">
                        @if($isDelivery)
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        @else
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        @endif
                        {{ $order->fulfillmentLabel() }}
                    </span>
                </div>
            </div>
            @if($isDelivery && $order->delivery_address)
                <div class="w-full sm:max-w-xl">
                    <p class="mb-2 text-sm font-bold uppercase tracking-wider text-teal-700">{{ __('Delivery address') }}</p>
                    <div class="rounded-lg border border-teal-300/80 bg-white/90 p-4 shadow-inner">
                        @if($order->delivery_pincode)
                            <p class="mb-2 font-mono text-sm font-bold text-teal-900">{{ $order->delivery_pincode }}</p>
                        @endif
                        <p class="whitespace-pre-wrap font-medium leading-relaxed text-teal-950">{{ $order->delivery_address }}</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endrole
