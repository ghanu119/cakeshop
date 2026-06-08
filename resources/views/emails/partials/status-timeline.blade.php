@php
    $steps = [
        'ordered' => __('Order placed'),
        'payment' => __('Payment verified'),
        'processing' => __('In kitchen'),
        'completed' => $order->isDeliveryFulfillment() ? __('Completed') : __('Ready for pickup'),
        'delivered' => __('Delivered'),
    ];

    $currentStep = match (true) {
        $order->order_status === 'cancelled' => 'cancelled',
        $order->isDelivered() => 'delivered',
        $order->order_status === 'completed' => 'completed',
        $order->order_status === 'processing' => 'processing',
        $order->isPaymentVerified() => 'payment',
        default => 'ordered',
    };

    $visibleSteps = $order->isDeliveryFulfillment()
        ? ['ordered', 'payment', 'processing', 'completed', 'delivered']
        : ['ordered', 'payment', 'processing', 'completed'];
@endphp
@if($order->order_status !== 'cancelled')
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px;">
    <tr>
        @foreach($visibleSteps as $index => $stepKey)
            @php
                $stepIndex = array_search($stepKey, $visibleSteps);
                $currentIndex = array_search($currentStep, $visibleSteps);
                $isActive = $currentIndex !== false && $stepIndex <= $currentIndex;
                $isCurrent = $stepKey === $currentStep;
            @endphp
            <td align="center" style="padding:8px 4px;vertical-align:top;">
                <div style="width:28px;height:28px;line-height:28px;border-radius:50%;margin:0 auto 6px;font-size:12px;font-weight:700;text-align:center;{{ $isActive ? 'background-color:#d97706;color:#ffffff;' : 'background-color:#e7e5e4;color:#78716c;' }}{{ $isCurrent ? 'box-shadow:0 0 0 3px #fde68a;' : '' }}">
                    {{ $index + 1 }}
                </div>
                <p style="margin:0;font-size:10px;font-weight:{{ $isCurrent ? '700' : '500' }};color:{{ $isCurrent ? '#b45309' : '#78716c' }};line-height:1.3;">
                    {{ $steps[$stepKey] }}
                </p>
            </td>
        @endforeach
    </tr>
</table>
@endif
