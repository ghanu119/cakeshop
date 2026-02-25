@component('mail::message')
# {{ __('Order status updated') }}

{{ __('Order reference') }}: **{{ $order->uuid }}**

{{ __('Current status') }}: **{{ $order->order_status }}**

@if($order->order_status === 'completed')
{{ __('Your order has been completed. Thank you!') }}
@elseif($order->order_status === 'cancelled')
{{ __('This order has been cancelled.') }}
@else
{{ __('We are processing your order.') }}
@endif

@endcomponent
