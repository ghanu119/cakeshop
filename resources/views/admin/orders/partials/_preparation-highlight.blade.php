@if($order->isProcessing() && $order->hasPreparationDeadline())
    @php
        $preparationAt = $order->preparation_at->setTimezone($tz);
        $prepOverdue = $preparationAt->isPast() && ! in_array($order->order_status, ['completed', 'cancelled', 'delivered']);
    @endphp
    <div class="flex items-center justify-between rounded-xl border border-sky-100 bg-sky-50 p-6 shadow-sm">
        <div>
            <p class="mb-1 text-sm font-bold uppercase tracking-wider text-sky-600">{{ __('Prepare by') }}</p>
            <h2 class="text-2xl font-bold text-sky-900">{{ $preparationAt->format('F d, Y') }}</h2>
            <p class="mt-0.5 text-lg font-medium text-sky-700">{{ $preparationAt->format('h:i A') }}</p>
        </div>
        <div class="text-right">
            <p class="mb-1 text-sm font-bold uppercase tracking-wider {{ $prepOverdue ? 'text-red-500' : 'text-sky-600' }}">
                {{ $prepOverdue ? __('Overdue by') : __('Time to prepare') }}
            </p>
            <p class="text-xl font-bold {{ $prepOverdue ? 'text-red-600' : 'text-sky-900' }}">
                {{ $preparationAt->diffForHumans(null, true) }}
            </p>
        </div>
    </div>
@endif
