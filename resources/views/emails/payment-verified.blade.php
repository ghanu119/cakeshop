@extends('emails.layouts.branded')

@section('title', __('Payment verified'))

@section('content')
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">{{ __('Payment verified') }}</h1>
<p style="margin:0 0 24px;font-size:15px;color:#57534e;">
    {{ __('Hi :name, your payment for order :ref has been verified.', ['name' => $order->guest_name, 'ref' => $order->order_no]) }}
</p>

@include('emails.partials.status-timeline', ['order' => $order])

<p style="margin:0 0 16px;font-size:14px;color:#57534e;">
    {{ __('Current order status') }}:
    @include('emails.partials.status-badge', ['label' => $order->orderStatusLabel(), 'variant' => $order->orderStatusBadgeVariant()])
</p>

@include('emails.partials.order-summary', ['order' => $order])

<div style="background-color:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;margin:0 0 24px;">
    <p style="margin:0;font-size:14px;color:#166534;line-height:1.6;">
        {{ __('We will schedule your order for preparation and keep you updated by email as the status changes.') }}
    </p>
</div>

@include('emails.partials.cta-button', [
    'url' => $order->customerOrderUrl(),
    'label' => __('View order'),
])
@endsection
