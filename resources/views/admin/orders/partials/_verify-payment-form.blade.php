@php
    $showQuery = array_filter([
        'view' => (! empty($fromToday) || request('view') === 'today') ? 'today' : null,
        'delivery_today' => (! empty($fromToday) || request()->boolean('delivery_today')) ? 1 : null,
    ]);
    $formAction = route('admin.orders.verify-payment', $order).($showQuery !== [] ? '?'.http_build_query($showQuery) : '');
    $hasOutstandingBalance = $order->hasOutstandingBalance();
    $verifyLabel = __('Verify payment');
    $confirmTitle = __('Verify payment?');
    $confirmMessage = $hasOutstandingBalance && $order->isInStoreOrder()
        ? __('The kitchen can proceed after verification, but ₹:amount will still be due. Verify payment anyway?', [
            'amount' => number_format($order->balanceDue(), 2),
        ])
        : __('Are you sure you want to verify payment for this order?');
    $buttonClass = $buttonClass ?? 'rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-70';
    $formClass = $formClass ?? 'shrink-0';
@endphp

<form
    method="post"
    action="{{ $formAction }}"
    class="{{ $formClass }}"
    data-verify-payment-form
    data-confirm-title="{{ $confirmTitle }}"
    data-confirm-message="{{ $confirmMessage }}"
    data-confirm-yes="{{ __('Yes, verify') }}"
    data-confirm-no="{{ __('Cancel') }}"
>
    @csrf
    <button type="submit" class="{{ $buttonClass }}">
        <span data-submit-label>{{ $verifyLabel }}</span>
        <span data-submitting-label class="hidden">{{ __('Verifying...') }}</span>
    </button>
</form>
