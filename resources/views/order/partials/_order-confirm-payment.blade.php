@php
    $currency = settings('currency') ?? 'INR';
    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $timezone = settings('timezone') ?? 'Asia/Kolkata';
    $paymentProofUrl = $order->getFirstMediaUrl('payment_proof');
    $paymentMadeAtDisplay = $order->payment_made_at
        ? $order->payment_made_at->timezone($timezone)->format('M d, Y, h:i A')
        : null;
@endphp

@if($order->isPaymentVerified())
    <div class="p-6 sm:p-10 bg-green-50/50">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-green-100 text-green-600 flex items-center justify-center shrink-0 border border-green-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-green-900 mb-2">{{ __('Payment verified') }}</h2>
                <p class="text-green-700 leading-relaxed">{{ __('Your payment has been successfully verified. We are processing your order and will get in touch if needed. Thank you!') }}</p>
            </div>
        </div>
    </div>
@elseif($order->hasPaymentDetailsSubmitted())
    <div class="p-6 sm:p-10 bg-blue-50/60 border-t border-blue-100">
        <div class="flex items-start gap-4 mb-6">
            <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center shrink-0 border border-blue-200">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-blue-900 mb-2">{{ __('Payment details received') }}</h2>
                <p class="text-blue-800 leading-relaxed">{{ __('Thank you. We have your payment information and are verifying it. Processing will begin once payment is confirmed. This usually takes a short while.') }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-blue-200/80 bg-white p-5 sm:p-6 space-y-4">
            <p class="text-xs font-bold text-stone-400 uppercase tracking-wider">{{ __('What you submitted') }}</p>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                @if($order->payment_reference)
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Transaction / UPI reference') }}</dt>
                        <dd class="mt-0.5 font-mono font-medium text-stone-900 break-all">{{ $order->payment_reference }}</dd>
                    </div>
                @endif
                @if($order->payment_amount !== null)
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Amount paid') }}</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ $symbol }}{{ number_format((float) $order->payment_amount, 2) }}</dd>
                    </div>
                @endif
                @if($paymentMadeAtDisplay)
                    <div>
                        <dt class="font-semibold text-stone-500">{{ __('Date & time of payment') }}</dt>
                        <dd class="mt-0.5 font-medium text-stone-900">{{ $paymentMadeAtDisplay }}</dd>
                    </div>
                @endif
                @if($paymentProofUrl)
                    <div class="sm:col-span-2">
                        <dt class="font-semibold text-stone-500 mb-2">{{ __('Payment proof') }}</dt>
                        <dd>
                            <a href="{{ $paymentProofUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block rounded-xl border border-stone-200 overflow-hidden shadow-sm hover:opacity-90 transition-opacity">
                                <img src="{{ $paymentProofUrl }}" alt="{{ __('Payment proof screenshot') }}" class="max-h-40 w-auto object-contain bg-stone-50" />
                            </a>
                        </dd>
                    </div>
                @endif
            </dl>
        </div>

        <p class="mt-6 text-sm text-stone-600 text-center sm:text-left">
            {{ __('Need to correct something? You can update your payment details.') }}
            <a href="{{ route('order.submit-payment', ['uuid' => $order->uuid]) }}" class="font-semibold text-blue-700 hover:text-blue-900 underline underline-offset-2">
                {{ __('Update payment details') }}
            </a>
        </p>
    </div>
@else
    <div class="p-6 sm:p-10">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
            </div>
            <div>
                <h3 class="text-xl font-bold text-stone-900">{{ __('Payment required') }}</h3>
                <p class="text-sm text-stone-500">{{ __('Please complete your payment to begin processing.') }}</p>
            </div>
        </div>

        <div class="bg-stone-50 rounded-2xl p-6 sm:p-8 mb-8 border border-stone-200">
            @php $paymentQrUrl = \App\Models\SiteSetting::first()?->getFirstMediaUrl('payment_qr'); @endphp
            @if($paymentQrUrl)
                <div class="flex flex-col items-center mb-6 border-b border-stone-200 pb-6">
                    <div class="bg-white p-3 rounded-2xl shadow-sm border border-stone-200 mb-3">
                        <img src="{{ $paymentQrUrl }}" alt="{{ __('Payment QR code') }}" class="w-40 h-40 object-contain" />
                    </div>
                    <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Scan to pay') }}</span>
                </div>
            @endif

            <div class="prose prose-sm max-w-none text-stone-600 leading-relaxed text-center sm:text-left">
                {!! nl2br(e(settings('payment_instructions') ?? __('Please pay via UPI to the number shown, or transfer to our bank account. Mention your order ID in the payment note.'))) !!}
            </div>
        </div>

        <div class="text-center">
            <p class="text-sm font-medium text-stone-600 mb-4">
                {!! nl2br(e(settings('payment_submit_instructions') ?? __('Already paid? Share your transaction details and screenshot with us.'))) !!}
            </p>
            <a href="{{ route('order.submit-payment', ['uuid' => $order->uuid]) }}" class="inline-flex w-full items-center justify-center rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 font-bold text-white shadow-[0_8px_20px_rgb(217,119,6,0.25)] hover:shadow-[0_12px_25px_rgb(217,119,6,0.35)] hover:-translate-y-0.5 transition-all text-lg gap-2">
                {{ __('Submit payment details') }}
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
@endif
