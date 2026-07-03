@php
    $symbol = $symbol ?? (settings('currency') === 'INR' ? '₹' : (settings('currency') ?? 'INR') . ' ');
    $timezone = settings('timezone') ?? 'Asia/Kolkata';
    $reference = $order->displayPaymentReference();
    $amount = $order->displayPaymentAmount();
    $paidAt = $order->displayPaymentMadeAt();
    $paidAtDisplay = $paidAt?->timezone($timezone)->format('M d, Y, h:i A');
    $referenceLabel = $order->isRazorpayPayment()
        ? __('Payment ID')
        : __('Transaction / UPI reference');
    $paymentProofUrl = $order->getFirstMediaUrl('payment_proof');
    $paidPayment = $order->paidPayment();
    $gatewayOrderId = $paidPayment?->gateway_order_id;
@endphp

@if($order->hasDisplayablePaymentDetails())
    <dl class="mt-6 space-y-4 rounded-xl border border-stone-200 bg-white/80 px-4 py-4 text-sm sm:px-5">
        <div>
            <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ __('Payment method') }}</dt>
            <dd class="mt-1 font-semibold text-stone-900">{{ $order->paymentMethodLabel() }}</dd>
        </div>

        @if($reference)
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ $referenceLabel }}</dt>
                <dd class="mt-1 break-all font-mono font-medium text-stone-900">{{ $reference }}</dd>
            </div>
        @endif

        @if($gatewayOrderId && $order->isRazorpayPayment())
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ __('Gateway order ID') }}</dt>
                <dd class="mt-1 break-all font-mono text-xs font-medium text-stone-700">{{ $gatewayOrderId }}</dd>
            </div>
        @endif

        @if($amount !== null)
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ __('Amount paid') }}</dt>
                <dd class="mt-1 font-semibold text-stone-900">{{ $symbol }}{{ number_format($amount, 2) }}</dd>
            </div>
        @endif

        @if($paidAtDisplay)
            <div>
                <dt class="text-xs font-bold uppercase tracking-wider text-stone-500">{{ __('Date & time of payment') }}</dt>
                <dd class="mt-1 font-medium text-stone-900">{{ $paidAtDisplay }}</dd>
            </div>
        @endif

        @if($paymentProofUrl)
            <div>
                <dt class="mb-2 text-xs font-bold uppercase tracking-wider text-stone-500">{{ __('Payment proof') }}</dt>
                <dd>
                    <a href="{{ $paymentProofUrl }}" target="_blank" rel="noopener noreferrer" class="inline-block overflow-hidden rounded-xl border border-stone-200 shadow-sm transition-opacity hover:opacity-90">
                        <img src="{{ $paymentProofUrl }}" alt="{{ __('Payment proof screenshot') }}" class="max-h-40 w-auto object-contain bg-stone-50" />
                    </a>
                </dd>
            </div>
        @endif
    </dl>
@endif
