@php
    $paymentCheckoutConfig = $paymentCheckoutConfig ?? ['enabled' => false, 'gateway' => null, 'key_id' => null];
    $symbol = $symbol ?? (settings('currency') === 'INR' ? '₹' : (settings('currency') ?? 'INR') . ' ');
    $messages = [
        'payment_cancelled' => __('payments.errors.payment_cancelled'),
        'payment_failed' => __('payments.errors.payment_failed'),
        'network_error' => __('payments.errors.network_error'),
        'gateway_not_configured' => __('payments.errors.gateway_not_configured'),
        'processing' => __('payments.processing'),
        'try_again' => __('payments.try_again'),
        'pay_now' => __('payments.pay_now'),
        'order_saved' => __('payments.order_saved', ['order_no' => $order->order_no]),
    ];
@endphp

@if($order->isPaymentVerified())
    <div class="p-6 sm:p-10 bg-green-50/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 border border-green-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-green-900 mb-2">{{ __('Payment verified') }}</h2>
                <p class="text-green-700 leading-relaxed">{{ __('payments.success.verified') }}</p>
            </div>
        </div>
    </div>
@else
    <div class="p-6 pb-10 sm:p-10 sm:pb-12">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-stone-900">{{ __('Payment required') }}</h3>
                <p class="text-sm text-stone-500">{{ __('Please complete your payment to begin processing.') }}</p>
            </div>
        </div>

        <p class="mb-2 text-sm font-medium text-stone-700">{{ $symbol }}{{ number_format($order->amount, 2) }} {{ __('due now') }}</p>
        <p class="mb-6 text-sm text-stone-600">{{ __('payments.order_saved', ['order_no' => $order->order_no]) }}</p>

        <div id="payment-error-container" class="mb-6"></div>

        @if($paymentCheckoutConfig['enabled'] ?? false)
            <div
                data-razorpay-checkout
                data-initiate-url="{{ route('order.payment.initiate', $order) }}"
                data-verify-url="{{ route('order.payment.verify', $order) }}"
                data-order-uuid="{{ $order->uuid }}"
                data-amount="{{ (int) round((float) $order->amount * 100) }}"
                data-currency="{{ settings('currency') ?? 'INR' }}"
                data-customer-name="{{ $order->guest_name }}"
                data-customer-email="{{ $order->guest_email ?? '' }}"
                data-customer-phone="{{ $order->guest_phone }}"
                data-key-id="{{ $paymentCheckoutConfig['key_id'] ?? '' }}"
                data-messages='@json($messages)'
            >
                <button
                    type="button"
                    data-razorpay-pay-btn
                    class="inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgb(217,119,6,0.25)] transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_25px_rgb(217,119,6,0.35)] disabled:cursor-not-allowed disabled:opacity-60"
                >
                    <span data-razorpay-pay-label>{{ __('payments.pay_now') }}</span>
                </button>
            </div>
        @else
            <div class="rounded-2xl border border-amber-200 bg-amber-50/80 px-5 py-4 text-sm leading-relaxed text-stone-700">
                <p>{{ __('payments.errors.gateway_not_configured') }}</p>
                <p class="mt-3">
                    <a href="{{ route('contact.index') }}" class="font-semibold text-amber-800 underline underline-offset-2 hover:text-amber-900">{{ __('payments.contact_us') }}</a>
                </p>
            </div>
        @endif
    </div>
@endif
