@extends('layouts.app')

@section('content')
    @include('order.partials._order-confirm-better-buns', [
        'order' => $order,
        'paymentCheckoutConfig' => $paymentCheckoutConfig ?? ['enabled' => false, 'gateway' => null, 'key_id' => null],
    ])
@endsection

@push('scripts')
    @vite(['resources/js/order-razorpay-checkout.js'])
@endpush
