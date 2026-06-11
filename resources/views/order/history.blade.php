@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order history') }}</h1>
    <p class="mb-6 text-gray-600">{{ __('Enter your order reference and phone number to view your order.') }}</p>

    <x-card class="mb-8">
        <form method="post" action="{{ route('order.history.search') }}" class="space-y-4">
            @csrf
            <x-form-errors show-system-errors :show-validation-summary="true" />
            <div>
                <label for="order_no" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Order reference') }} *</label>
                <x-input type="text" name="order_no" id="order_no" value="{{ old('order_no', $order_no ?? '') }}" class="block w-full" placeholder="e.g. BB-20260606-001" required />
                @error('order_no')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone number') }} *</label>
                <x-input type="text" name="phone" id="phone" value="{{ old('phone', $phone ?? '') }}" class="block w-full" placeholder="{{ __('e.g. 9876543210') }}" required />
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-button type="submit" variant="primary">{{ __('Look up order') }}</x-button>
        </form>
    </x-card>

    @if(isset($order))
        <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Your order') }}</h2>
        <x-card class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <p class="font-medium text-gray-900">{{ $order->displayProductName() }}</p>
                @include('order.partials._order-options', ['order' => $order])
                <p class="text-sm text-gray-500">{{ __('Reference') }}: <span class="font-mono">{{ $order->order_no }}</span></p>
                <p class="text-sm text-gray-600">{{ __('Ordered') }}: {{ $order->ordered_at?->format('d M Y H:i') }} · {{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}</p>
                <div class="mt-2 flex flex-wrap gap-2">
                    <x-badge :variant="$order->payment_status === 'verified' ? 'success' : 'warning'">{{ $order->payment_status === 'verified' ? __('Payment verified') : __('Payment pending') }}</x-badge>
                    <x-badge variant="default">{{ ucfirst($order->order_status ?? 'pending') }}</x-badge>
                </div>
            </div>
            <a href="{{ route('order.confirm', $order) }}" class="inline-flex shrink-0 items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">
                {{ __('View order') }}
            </a>
        </x-card>
    @endif

    <p class="mt-8 text-center text-sm text-gray-500">
        <a href="{{ route('home') }}" class="text-gray-600 hover:underline">{{ __('Back to home') }}</a>
    </p>
</div>
@endsection
