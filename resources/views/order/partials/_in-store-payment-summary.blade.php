@php
    $symbol = $symbol ?? '₹';
    $verifiedWithBalance = $order->isVerifiedWithOutstandingBalance();
    $fullyPaid = $order->isInStoreOrder() && $order->isPaymentVerified() && ! $order->hasOutstandingBalance();
@endphp

@if($order->isInStoreOrder())
    <div class="p-6 sm:p-10 {{ $fullyPaid ? 'bg-violet-50/50' : 'bg-amber-50/50' }}">
        <div class="flex items-start gap-4">
            <div @class([
                'flex h-12 w-12 shrink-0 items-center justify-center rounded-full border',
                'border-violet-200 bg-violet-100 text-violet-600' => $fullyPaid,
                'border-amber-200 bg-amber-100 text-amber-600' => ! $fullyPaid,
            ])>
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 @class([
                    'mb-2 text-xl font-bold',
                    'text-violet-900' => $fullyPaid,
                    'text-amber-900' => ! $fullyPaid,
                ])>
                    @if($fullyPaid)
                        {{ __('In-store payment collected') }}
                    @elseif($verifiedWithBalance)
                        {{ __('Payment verified — balance due on pickup') }}
                    @elseif($order->isPartiallyPaid())
                        {{ __('Partially paid — balance due on pickup') }}
                    @else
                        {{ __('Payment pending — collect on pickup') }}
                    @endif
                </h2>
                <p @class([
                    'leading-relaxed',
                    'text-violet-800' => $fullyPaid,
                    'text-amber-800' => ! $fullyPaid,
                ])>
                    @if($fullyPaid)
                        {{ __('Full payment has been received for this in-store order.') }}
                    @elseif($verifiedWithBalance)
                        {{ __('Payment is verified and the kitchen can prepare your order. Please pay the remaining balance when you collect it.') }}
                    @else
                        {{ __('The kitchen can start preparing this order. Collect the remaining balance before handover.') }}
                    @endif
                </p>

                <dl class="mt-5 grid grid-cols-1 gap-3 rounded-2xl border border-white/80 bg-white/70 p-4 text-sm sm:grid-cols-3">
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Order total') }}</dt>
                        <dd class="mt-0.5 text-base font-bold text-stone-900">{{ $symbol }}{{ number_format($order->amount, 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Cash received') }}</dt>
                        <dd class="mt-0.5 text-base font-bold text-violet-800">{{ $symbol }}{{ number_format($order->totalCashReceived(), 2) }}</dd>
                    </div>
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Balance due') }}</dt>
                        <dd @class([
                            'mt-0.5 text-base font-bold',
                            'text-green-700' => $order->balanceDue() <= 0.01,
                            'text-amber-700' => $order->balanceDue() > 0.01,
                        ])>{{ $symbol }}{{ number_format($order->balanceDue(), 2) }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endif
