@if($order->isInStoreOrder())
    @include('order.partials._in-store-payment-summary', ['order' => $order, 'symbol' => $symbol ?? '₹'])
@elseif($order->isPaymentVerified())
    <div class="p-6 sm:p-10 bg-green-50/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 border border-green-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div class="min-w-0 flex-1">
                <h2 class="text-xl font-bold text-green-900 mb-2">{{ __('Payment verified') }}</h2>
                <p class="text-green-700 leading-relaxed">{{ __('payments.success.verified') }}</p>
                @include('order.partials._payment-details-summary', [
                    'order' => $order,
                    'symbol' => $symbol ?? null,
                ])
            </div>
        </div>
    </div>
@elseif($order->payment_method === \App\Models\Order::PAYMENT_METHOD_RAZORPAY)
    @include('order.partials._order-confirm-razorpay-payment', [
        'order' => $order,
        'paymentCheckoutConfig' => $paymentCheckoutConfig ?? ['enabled' => false, 'gateway' => null, 'key_id' => null],
        'symbol' => $symbol ?? null,
    ])
@else
    @include('order.partials._order-confirm-payment', ['order' => $order])
@endif
