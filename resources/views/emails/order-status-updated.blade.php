@component('mail::message')
# {{ __('Order status updated') }}

{{ __('Order reference') }}: **{{ $order->order_no }}**

{{ __('Current status') }}: **{{ $order->order_status }}**

@if($order->order_status === 'delivered')
{{ __('Your order has been delivered. Thank you!') }}
@elseif($order->order_status === 'completed')
{{ __('Your order has been completed. Thank you!') }}
@elseif($order->order_status === 'cancelled')
{{ __('This order has been cancelled.') }}
@elseif($order->order_status === 'processing' && $order->preparation_at)
@php $prepTz = settings('timezone') ?? 'Asia/Kolkata'; @endphp
{{ __('We are preparing your order.') }}

{{ __('Target preparation time') }}: **{{ $order->preparation_at->setTimezone($prepTz)->format('M d, Y h:i A') }}** ({{ $prepTz }})
@else
{{ __('We are processing your order.') }}
@endif

@endcomponent
