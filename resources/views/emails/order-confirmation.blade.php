@extends('emails.layouts.branded')

@section('title', __('Order confirmed'))

@section('content')
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">{{ __('Order confirmed') }}</h1>
<p style="margin:0 0 24px;font-size:15px;color:#57534e;">
    {{ __('Hi :name, thank you for your order!', ['name' => $order->guest_name]) }}
</p>

@include('emails.partials.status-timeline', ['order' => $order])

@include('emails.partials.order-summary', ['order' => $order])

<div style="background-color:#fffbeb;border:1px solid #fde68a;border-radius:12px;padding:16px 20px;margin:0 0 24px;">
    <p style="margin:0 0 8px;font-size:14px;font-weight:700;color:#92400e;">{{ __('Next step: submit payment') }}</p>
    <p style="margin:0;font-size:14px;color:#78350f;line-height:1.6;">
        {{ __('Please submit your payment details so we can verify and start preparing your order.') }}
    </p>
</div>

@include('emails.partials.cta-button', [
    'url' => route('order.confirm', $order),
    'label' => __('View order'),
])

<p style="margin:0;font-size:13px;color:#78716c;text-align:center;">
    {{ __('You can also submit payment from your order page using the link above.') }}
</p>
@endsection
