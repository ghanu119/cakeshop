@extends('emails.layouts.branded')

@section('title', __('Payment submitted'))

@section('content')
@php
    $timezone = \App\Models\Order::shopTimezone();
    $paymentAtDisplay = $order->payment_made_at
        ? $order->payment_made_at->timezone($timezone)->format('M d, Y h:i A')
        : null;
@endphp
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">
    {{ $isUpdate ? __('Payment details updated') : __('Payment submitted') }}
</h1>
<p style="margin:0 0 24px;font-size:15px;color:#57534e;">
    {{ __('Order :ref is awaiting payment verification.', ['ref' => $order->order_no]) }}
</p>

@include('emails.partials.order-summary', ['order' => $order])

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#1e40af;">{{ __('Payment details') }}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                @if($order->payment_reference)
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#1e40af;width:140px;">{{ __('Reference') }}</td>
                    <td style="padding:4px 0;font-size:14px;font-weight:600;color:#1c1917;">{{ $order->payment_reference }}</td>
                </tr>
                @endif
                @if($order->payment_amount !== null)
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#1e40af;">{{ __('Amount paid') }}</td>
                    <td style="padding:4px 0;font-size:14px;font-weight:600;color:#1c1917;">₹ {{ number_format($order->payment_amount, 2) }}</td>
                </tr>
                @endif
                @if($paymentAtDisplay)
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#1e40af;">{{ __('Payment date') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $paymentAtDisplay }} ({{ $timezone }})</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#1e40af;">{{ __('Proof uploaded') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $order->hasMedia('payment_proof') ? __('Yes') : __('No') }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@include('emails.partials.cta-button', [
    'url' => $order->adminOrderUrl(),
    'label' => __('Verify payment'),
])
@endsection
