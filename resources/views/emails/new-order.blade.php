@extends('emails.layouts.branded')

@section('title', __('New order'))

@section('content')
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">{{ __('New order received') }}</h1>
<p style="margin:0 0 24px;font-size:15px;color:#57534e;">
    {{ __('A customer has placed a new order.') }}
</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fafaf9;border-radius:12px;border:1px solid #e7e5e4;margin:0 0 24px;">
    <tr>
        <td style="padding:20px;">
            <p style="margin:0 0 12px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#78716c;">{{ __('Customer') }}</p>
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;width:100px;">{{ __('Name') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $order->guest_name }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;">{{ __('Phone') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;">{{ $order->guest_phone }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;font-size:14px;color:#78716c;">{{ __('Email') }}</td>
                    <td style="padding:4px 0;font-size:14px;color:#1c1917;"><a href="mailto:{{ $order->guest_email }}" style="color:#b45309;text-decoration:none;">{{ $order->guest_email }}</a></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@include('emails.partials.order-summary', ['order' => $order])

@include('emails.partials.cta-button', [
    'url' => $order->adminOrderUrl(),
    'label' => __('Review in admin'),
])
@endsection
