@extends('emails.layouts.branded')

@section('title', __('Order status updated'))

@section('content')
<h1 style="margin:0 0 8px;font-size:24px;font-weight:700;color:#1c1917;">{{ __('Order status updated') }}</h1>
<p style="margin:0 0 8px;font-size:15px;color:#57534e;">
    {{ __('Hi :name, your order :ref has been updated.', ['name' => $order->guest_name, 'ref' => $order->order_no]) }}
</p>
@if($previousStatus && $previousStatus !== $order->order_status)
<p style="margin:0 0 24px;font-size:14px;color:#78716c;">
    {{ __('Changed from :from to :to.', ['from' => ucfirst($previousStatus), 'to' => $order->orderStatusLabel()]) }}
</p>
@else
<p style="margin:0 0 24px;font-size:14px;color:#78716c;">
    {{ __('Current status') }}:
    @include('emails.partials.status-badge', ['label' => $order->orderStatusLabel(), 'variant' => $order->orderStatusBadgeVariant()])
</p>
@endif

@include('emails.partials.status-timeline', ['order' => $order])

@if(in_array($order->order_status, ['delivered', 'completed', 'cancelled', 'pending'], true))
<div style="background-color:#fafaf9;border-radius:12px;border:1px solid #e7e5e4;padding:16px 20px;margin:0 0 24px;">
    @if($order->order_status === 'delivered')
        <p style="margin:0;font-size:15px;color:#166534;font-weight:600;">{{ __('Your order has been delivered. Thank you!') }}</p>
    @elseif($order->order_status === 'completed')
        <p style="margin:0;font-size:15px;color:#166534;font-weight:600;">
            {{ $order->isDeliveryFulfillment()
                ? __('Your order is complete and ready for delivery.')
                : __('Your order is ready for pickup. Thank you!') }}
        </p>
    @elseif($order->order_status === 'cancelled')
        <p style="margin:0;font-size:15px;color:#991b1b;font-weight:600;">{{ __('This order has been cancelled.') }}</p>
        <p style="margin:12px 0 0;font-size:14px;color:#57534e;">{{ __('If you have questions, please contact us.') }}</p>
    @else
        <p style="margin:0;font-size:15px;color:#92400e;font-weight:600;">{{ __('Your order has been received and is awaiting kitchen scheduling.') }}</p>
    @endif
</div>
@endif

@include('emails.partials.order-summary', ['order' => $order])

@include('emails.partials.cta-button', [
    'url' => $order->customerOrderUrl(),
    'label' => __('View order'),
])
@endsection
