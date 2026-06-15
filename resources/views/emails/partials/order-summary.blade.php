@php
    $timezone = \App\Models\Order::shopTimezone();
    $scheduledLabel = $order->isDeliveryFulfillment() ? __('Delivery scheduled') : __('Pickup scheduled');
    $scheduledAtDisplay = $order->delivery_at
        ? $order->delivery_at->timezone($timezone)->format('M d, Y').' · '.$order->delivery_at->timezone($timezone)->format('h:i A')
        : null;
    $orderedAtDisplay = $order->ordered_at
        ? $order->ordered_at->timezone($timezone)->format('M d, Y h:i A')
        : null;
@endphp
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafaf9;border-radius:12px;border:1px solid #e7e5e4;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#78716c;">{{ __('Order summary') }}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;width:140px;vertical-align:top;">{{ __('Reference') }}</td>
                    <td style="padding:6px 0;font-size:14px;font-weight:700;color:#1c1917;">{{ $order->order_no }}</td>
                </tr>
                @if($orderedAtDisplay)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Ordered') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $orderedAtDisplay }} ({{ $timezone }})</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Product') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->displayProductName() }}</td>
                </tr>
                @if($order->hasVariantSnapshot())
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Weight') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->variant_summary }}</td>
                </tr>
                @endif
                @if($order->hasFlavorSnapshot())
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Flavor') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->displayFlavorName() }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Quantity') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->quantity }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Unit price') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">₹ {{ number_format($order->displayUnitPrice(), 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Amount') }}</td>
                    <td style="padding:6px 0;font-size:16px;font-weight:700;color:#b45309;">₹ {{ number_format($order->amount, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Order type') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->fulfillmentLabel() }}</td>
                </tr>
                @if($scheduledAtDisplay)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ $scheduledLabel }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $scheduledAtDisplay }}</td>
                </tr>
                @endif
                @if($order->isDeliveryFulfillment() && $order->delivery_address)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Pincode') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;">{{ $order->delivery_pincode ?: '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Address') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;white-space:pre-wrap;">{{ $order->delivery_address }}</td>
                </tr>
                @endif
                @if($order->message_on_cake)
                <tr>
                    <td style="padding:6px 0;font-size:14px;color:#78716c;vertical-align:top;">{{ __('Cake message') }}</td>
                    <td style="padding:6px 0;font-size:14px;color:#1c1917;font-style:italic;">"{{ $order->message_on_cake }}"</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
