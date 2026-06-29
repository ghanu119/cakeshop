@php
    $canSetPreparationTime = $canSetPreparationTime ?? auth()->user()->hasRole('Admin');
    $paymentBadge = $paymentBadge ?? null;
    $paymentBadgeLabel = $paymentBadgeLabel ?? __('Payment verified');
    $paymentBadgeInStore = $paymentBadgeInStore ?? false;
    $tz = $preparationRules['timezone'];
    $deliveryPast = $preparationRules['deliveryPast'] ?? false;
    $prepMin = $preparationRules['min']->format('Y-m-d\TH:i');
    $prepMax = $preparationRules['max']?->format('Y-m-d\TH:i');
    $prepDefault = $order->preparation_at?->setTimezone($tz)->format('Y-m-d\TH:i');
    if (! $prepDefault && $deliveryPast) {
        $prepDefault = $preparationRules['min']->format('Y-m-d\TH:i');
    }
    $prepValue = old('preparation_at', $prepDefault);
    $currentStatus = old('order_status', $order->order_status);
    $showPrepPanel = $canSetPreparationTime && $currentStatus === 'processing';
    $kitchenStatusOptions = ['completed', 'cancelled'];
    $showDeliveredOption = $canSetPreparationTime && $order->isDeliveryFulfillment() && ! $order->isStatusLocked();
    $statusFormQuery = array_filter([
        'from' => ! empty($fromKitchen) ? 'kitchen' : null,
        'delivery_today' => (! empty($fromToday) || request()->boolean('delivery_today')) ? 1 : null,
        'view' => (! empty($fromToday) || request('view') === 'today') ? 'today' : null,
    ]);
@endphp

@if($order->isStatusLocked())
    <div class="flex flex-wrap items-center justify-end gap-3">
        @if($paymentBadge === 'verified')
            <span @class([
                'inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-semibold shadow-sm',
                'border-violet-200 bg-violet-50 text-violet-800' => $paymentBadgeInStore,
                'border-emerald-200 bg-emerald-50 text-emerald-700' => ! $paymentBadgeInStore,
            ])>
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $paymentBadgeLabel }}
            </span>
        @endif
        <span class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-sm font-semibold text-teal-800 shadow-sm">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ __('Delivered') }}
        </span>
    </div>
@else
<div id="order-status" class="w-full md:w-auto">
    <form
        method="post"
        action="{{ $statusFormAction }}{{ $statusFormQuery !== [] ? '?' . http_build_query($statusFormQuery) : '' }}"
        class="flex w-full flex-col gap-3 md:w-auto md:items-end"
        data-order-status-form
        data-initial-order-status="{{ $order->order_status }}"
        data-status-confirm-title="{{ __('Change order status?') }}"
        data-status-confirm-message="{{ __('Are you sure you want to change the order status to :status?') }}"
        data-status-confirm-yes="{{ __('Yes, update') }}"
        data-status-confirm-no="{{ __('Cancel') }}"
        @if($canSetPreparationTime) data-allow-preparation="true" @endif
    >
        @csrf

        <div class="flex flex-wrap items-center justify-end gap-3">
            @if($paymentBadge === 'verified')
                <span @class([
                    'inline-flex shrink-0 items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-semibold shadow-sm',
                    'border-violet-200 bg-violet-50 text-violet-800' => $paymentBadgeInStore,
                    'border-emerald-200 bg-emerald-50 text-emerald-700' => ! $paymentBadgeInStore,
                ])>
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $paymentBadgeLabel }}
                </span>
            @endif

            <div class="flex flex-wrap items-center gap-2">
                @if(!$canSetPreparationTime)
                    <span class="inline-flex items-center rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 text-sm font-semibold capitalize text-blue-800">
                        {{ __($order->order_status) }}
                    </span>
                @endif
                <select
                    name="order_status"
                    data-order-status-select
                    class="block w-44 cursor-pointer rounded-lg border border-gray-300 py-2 pl-3 pr-10 text-sm font-medium text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                >
                    @if($canSetPreparationTime)
                        <option value="pending" @selected($currentStatus === 'pending')>{{ __('Pending') }}</option>
                        <option value="processing" @selected($currentStatus === 'processing')>{{ __('Processing') }}</option>
                        <option value="completed" @selected($currentStatus === 'completed')>{{ __('Completed') }}</option>
                        @if($showDeliveredOption)
                            <option value="delivered" @selected($currentStatus === 'delivered')>{{ __('Delivered') }}</option>
                        @endif
                        <option value="cancelled" @selected($currentStatus === 'cancelled')>{{ __('Cancelled') }}</option>
                    @else
                        @foreach($kitchenStatusOptions as $status)
                            <option value="{{ $status }}" @selected($currentStatus === $status)>{{ __(ucfirst($status)) }}</option>
                        @endforeach
                    @endif
                </select>
                <button
                    type="submit"
                    data-order-status-submit
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:cursor-not-allowed disabled:opacity-70"
                >
                    <span data-submit-label>{{ __('Update') }}</span>
                    <span data-submitting-label class="hidden">{{ __('Updating...') }}</span>
                </button>
            </div>
        </div>

        @if($canSetPreparationTime)
            <div
                data-preparation-panel
                class="w-full max-w-md overflow-hidden rounded-xl border border-sky-200 bg-gradient-to-br from-sky-50 to-indigo-50/80 p-4 shadow-sm transition-all duration-300 {{ $showPrepPanel ? '' : 'hidden' }}"
            >
                <div class="mb-3 flex items-start gap-2">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-sky-100 text-sky-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <p class="text-sm font-bold text-sky-900">{{ __('Prepare by') }}</p>
                        <p class="mt-0.5 text-xs text-sky-700/90">{{ __('When the kitchen must finish this order') }} ({{ $tz }})</p>
                    </div>
                </div>
                <label for="preparation_at" class="sr-only">{{ __('Prepare by') }}</label>
                <input
                    type="datetime-local"
                    name="preparation_at"
                    id="preparation_at"
                    value="{{ $prepValue }}"
                    min="{{ $prepMin }}"
                    @if($prepMax) max="{{ $prepMax }}" @endif
                    data-preparation-input
                    class="block w-full rounded-lg border border-sky-200 bg-white px-3 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-sky-500 focus:ring-sky-500 disabled:bg-gray-100 disabled:text-gray-400"
                    {{ $showPrepPanel ? '' : 'disabled' }}
                />
                @error('preparation_at')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @if($deliveryPast)
                    <p class="mt-2 text-xs text-amber-800/90">{{ __('Delivery time has passed. Set prepare-by to now so the kitchen can start immediately.') }}</p>
                @else
                    <p class="mt-2 text-xs text-sky-800/80">{{ __("Kitchen will see this order in Today's orders after you update.") }}</p>
                @endif
            </div>
        @else
            <p class="max-w-md text-right text-xs text-gray-500">{{ __('Preparation time is set by an administrator.') }}</p>
        @endif
    </form>
</div>

@endif
