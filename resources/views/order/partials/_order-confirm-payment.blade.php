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
            <a href="{{ route('order.submit-payment', $order) }}" class="font-semibold text-blue-700 hover:text-blue-900 underline underline-offset-2">
                {{ __('Update payment details') }}
            </a>
        </p>
    </div>
@else
    <div class="p-6 pb-10 sm:p-10 sm:pb-12">
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
            @php
                $paymentUpiId = trim((string) (settings('payment_upi_id') ?? ''));
                $paymentQrUrl = \App\Models\SiteSetting::first()?->getFirstMediaUrl('payment_qr');
                $upiPayUrl = null;
                $upiAppOptions = [];
                if ($paymentUpiId !== '') {
                    $upiPayParams = [
                        'pa' => $paymentUpiId,
                        'pn' => settings('site_name') ?: config('app.name'),
                        'am' => number_format((float) $order->amount, 2, '.', ''),
                        'cu' => settings('currency') ?? 'INR',
                        'tn' => $order->order_no,
                    ];
                    $upiQuery = http_build_query($upiPayParams, '', '&', PHP_QUERY_RFC3986);
                    $upiPayUrl = 'upi://pay?' . $upiQuery;
                    $upiAppOptions = [
                        ['label' => __('Google Pay'), 'url' => 'gpay://upi/pay?' . $upiQuery],
                        ['label' => __('PhonePe'), 'url' => 'phonepe://pay?' . $upiQuery],
                        ['label' => __('Paytm'), 'url' => 'paytmmp://pay?' . $upiQuery],
                        ['label' => __('BHIM UPI'), 'url' => 'bhim://upi/pay?' . $upiQuery],
                        ['label' => __('Amazon Pay'), 'url' => 'amazonpay://upi/pay?' . $upiQuery],
                    ];
                    $upiPayConfigJson = json_encode([
                        'query' => $upiQuery,
                        'url' => $upiPayUrl,
                        'apps' => $upiAppOptions,
                    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
                }
            @endphp

            @if($paymentQrUrl)
                <div class="flex flex-col items-center gap-4">
                    <div class="bg-white p-3 rounded-2xl shadow-sm border border-stone-200">
                        <img src="{{ $paymentQrUrl }}" alt="{{ __('Payment QR code') }}" class="w-40 h-40 object-contain" />
                    </div>
                    <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">{{ __('Scan to pay') }}</span>
                    <a
                        href="{{ route('order.payment-qr.download') }}"
                        class="payment-block-action inline-flex items-center gap-1.5 rounded-full border border-stone-200 bg-white px-5 py-2.5 text-xs font-semibold leading-none text-stone-600 shadow-sm transition-colors hover:border-stone-300 hover:bg-stone-50 hover:text-stone-900"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        {{ __('Download QR code') }}
                    </a>
                </div>
                @if($paymentUpiId !== '' || filled(settings('payment_instructions')))
                    <div class="payment-block-divider" role="presentation"></div>
                @endif
            @endif

            @if($paymentUpiId !== '')
                <div class="flex flex-col gap-3">
                    <p class="text-xs font-bold uppercase tracking-wider text-stone-500 text-center sm:text-left">{{ __('UPI ID') }}</p>
                    <div class="flex flex-col items-center gap-3 sm:items-start">
                        <p class="break-all font-mono text-base font-semibold text-stone-900">{{ $paymentUpiId }}</p>
                        <div class="payment-block-action">
                            @include('order.partials._copy-button', [
                                'text' => $paymentUpiId,
                                'label' => __('Copy UPI ID'),
                                'toast' => __('UPI ID copied!'),
                            ])
                        </div>
                    </div>
                </div>
                @if(filled(settings('payment_instructions')))
                    <div class="payment-block-divider" role="presentation"></div>
                @endif
            @endif

            <div class="payment-block-instructions prose prose-sm max-w-none text-stone-600 leading-relaxed text-center sm:text-left">
                {!! nl2br(e(settings('payment_instructions') ?? __('Please pay via UPI to the number shown, or transfer to our bank account. Mention your order ID in the payment note.'))) !!}
            </div>
        </div>

        <div class="payment-confirm-actions text-center">
            @if($paymentUpiId !== '')
                <script type="application/json" id="upi-pay-config">{!! $upiPayConfigJson !!}</script>
                <div class="mb-6">
                    <a
                        href="{{ $upiPayUrl }}"
                        data-upi-pay-button
                        class="payment-upi-app-btn"
                    >
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ __('Pay with UPI app') }}</span>
                    </a>
                    <p class="mt-2 text-xs text-stone-500">{{ __('Choose your UPI app — amount and order number are pre-filled.') }}</p>
                </div>
            @endif

            <p class="mb-4 text-sm font-medium text-stone-600">
                {!! nl2br(e(settings('payment_submit_instructions') ?? __('Already paid? Share your transaction details and screenshot with us.'))) !!}
            </p>
            <a href="{{ route('order.submit-payment', $order) }}" class="payment-submit-btn inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-bold text-white shadow-[0_8px_20px_rgb(217,119,6,0.25)] transition-all hover:-translate-y-0.5 hover:shadow-[0_12px_25px_rgb(217,119,6,0.35)]">
                <span>{{ __('Submit payment details') }}</span>
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>

    @once
        @push('styles')
            <style>
                .payment-confirm-actions {
                    padding-bottom: 0.5rem;
                }
                a.payment-upi-app-btn,
                button.payment-upi-app-btn {
                    display: inline-flex !important;
                    width: 100%;
                    align-items: center;
                    justify-content: center;
                    gap: 0.5rem;
                    border: none;
                    border-radius: 1rem;
                    padding: 1rem 2rem;
                    font-size: 1.125rem;
                    line-height: 1.75rem;
                    font-weight: 700;
                    color: #fff !important;
                    text-decoration: none !important;
                    background: linear-gradient(to right, #059669, #0d9488) !important;
                    box-shadow: 0 8px 20px rgb(16 185 129 / 0.25);
                    transition: transform 0.15s ease, box-shadow 0.15s ease;
                    cursor: pointer;
                }
                a.payment-upi-app-btn:hover,
                button.payment-upi-app-btn:hover {
                    color: #fff !important;
                    text-decoration: none !important;
                    transform: translateY(-2px);
                    box-shadow: 0 12px 25px rgb(16 185 129 / 0.35);
                }
                a.payment-submit-btn {
                    margin-bottom: 0.25rem;
                }
                .upi-app-sheet {
                    position: fixed;
                    inset: 0;
                    z-index: 200;
                    display: flex;
                    align-items: flex-end;
                    justify-content: center;
                    padding: 1rem;
                    background: rgb(0 0 0 / 0.45);
                }
                .upi-app-sheet[hidden] {
                    display: none !important;
                }
                .upi-app-sheet__panel {
                    width: 100%;
                    max-width: 28rem;
                    border-radius: 1.25rem 1.25rem 0 0;
                    background: #fff;
                    padding: 1rem 1rem 1.25rem;
                    box-shadow: 0 -8px 30px rgb(0 0 0 / 0.15);
                }
                .upi-app-sheet__title {
                    margin: 0 0 0.25rem;
                    font-size: 1.125rem;
                    font-weight: 700;
                    color: #1c1917;
                    text-align: center;
                }
                .upi-app-sheet__subtitle {
                    margin: 0 0 1rem;
                    font-size: 0.75rem;
                    line-height: 1.125rem;
                    color: #78716c;
                    text-align: center;
                }
                .upi-app-sheet__list {
                    display: flex;
                    flex-direction: column;
                    gap: 0.5rem;
                }
                .upi-app-sheet__option {
                    display: flex !important;
                    align-items: center;
                    justify-content: center;
                    width: 100%;
                    padding: 0.875rem 1rem;
                    border-radius: 0.875rem;
                    border: 1px solid #e7e5e4;
                    background: #fafaf9;
                    color: #1c1917 !important;
                    font-size: 0.9375rem;
                    font-weight: 600;
                    text-decoration: none !important;
                }
                .upi-app-sheet__option:hover {
                    background: #fff;
                    border-color: #059669;
                    color: #1c1917 !important;
                }
                .upi-app-sheet__cancel {
                    display: block;
                    width: 100%;
                    margin-top: 0.75rem;
                    padding: 0.875rem 1rem;
                    border: none;
                    border-radius: 0.875rem;
                    background: #f5f5f4;
                    color: #57534e;
                    font-size: 0.9375rem;
                    font-weight: 600;
                    cursor: pointer;
                }
            </style>
        @endpush
        @push('scripts')
            <div id="upi-app-sheet" class="upi-app-sheet" hidden aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="upi-app-sheet-title">
                <div class="upi-app-sheet__panel" role="document" data-upi-app-sheet-panel>
                    <p id="upi-app-sheet-title" class="upi-app-sheet__title">{{ __('Choose UPI app') }}</p>
                    <p class="upi-app-sheet__subtitle">{{ __('Select the app you want to pay with.') }}</p>
                    <div class="upi-app-sheet__list" data-upi-app-list></div>
                    <button type="button" class="upi-app-sheet__cancel" data-upi-app-sheet-close>{{ __('Cancel') }}</button>
                </div>
            </div>
        @endpush
    @endonce
@endif
