@component('mail::message')
# {{ __('New order') }}

{{ __('Order reference') }}: **{{ $order->uuid }}**

- {{ __('Guest') }}: {{ $order->guest_name }} ({{ $order->guest_phone }})
- {{ __('Product') }}: {{ $order->displayProductName() }}
@if($order->hasVariantSnapshot())
- {{ __('Weight') }}: {{ $order->variant_summary }}
@endif
- {{ __('Quantity') }}: {{ $order->quantity }}
- {{ __('Unit price') }}: ₹ {{ number_format($order->displayUnitPrice(), 2) }}
- {{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}

@component('mail::button', ['url' => route('admin.orders.show', $order)])
{{ __('View order') }}
@endcomponent

@endcomponent
