@php
    $showQuery = array_filter([
        'view' => (! empty($fromToday) || request('view') === 'today') ? 'today' : null,
        'delivery_today' => (! empty($fromToday) || request()->boolean('delivery_today')) ? 1 : null,
    ]);
    $formAction = route('admin.orders.record-cash-payment', $order).($showQuery !== [] ? '?'.http_build_query($showQuery) : '');
@endphp

<form
    method="post"
    action="{{ $formAction }}"
    class="mt-4 rounded-lg border border-amber-200 bg-amber-50/60 p-4"
    data-record-cash-payment-form
    data-confirm-title="{{ __('Record cash payment?') }}"
    data-confirm-message="{{ __('Record :amount received for this order?', ['amount' => '₹:amount']) }}"
    data-confirm-yes="{{ __('Yes, record payment') }}"
    data-confirm-no="{{ __('Cancel') }}"
    data-balance-due="{{ number_format($order->balanceDue(), 2, '.', '') }}"
>
    @csrf
    <p class="text-sm font-semibold text-amber-900">{{ __('Collect remaining balance') }}</p>
    <p class="mt-1 text-sm text-amber-800">{{ __('Balance due: :amount', ['amount' => '₹ '.number_format($order->balanceDue(), 2)]) }}</p>

    <label for="amount_received" class="mb-1 mt-4 block text-sm font-medium text-gray-700">{{ __('Amount received now') }}</label>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
        <div class="flex-1">
            <input
                type="number"
                name="amount_received"
                id="amount_received"
                min="0.01"
                max="{{ number_format($order->balanceDue(), 2, '.', '') }}"
                step="0.01"
                value="{{ old('amount_received', number_format($order->balanceDue(), 2, '.', '')) }}"
                required
                class="block w-full rounded-lg border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
            />
            @error('amount_received')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        <button
            type="submit"
            class="inline-flex shrink-0 items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-70"
        >
            <span data-submit-label>{{ __('Record payment') }}</span>
            <span data-submitting-label class="hidden">{{ __('Recording...') }}</span>
        </button>
    </div>
</form>
