@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order history') }}</h1>
    <p class="mb-6 text-gray-600">{{ __('Enter your phone number to see your cake orders. You can open any order to view status or submit payment.') }}</p>

    <x-card class="mb-8">
        <form method="post" action="{{ route('order.history.search') }}" class="space-y-4">
            @csrf
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone number') }} *</label>
                <x-input type="text" name="phone" id="phone" value="{{ old('phone', $phone ?? '') }}" class="block w-full" placeholder="{{ __('e.g. 9876543210') }}" required />
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-button type="submit" variant="primary">{{ __('Search orders') }}</x-button>
        </form>
    </x-card>

    @if(isset($orders))
        @if($orders->isEmpty())
            <x-card>
                <p class="text-gray-600">{{ __('No orders found for this phone number.') }}</p>
            </x-card>
        @else
            <h2 class="mb-4 text-xl font-semibold text-gray-900">{{ __('Your orders') }}</h2>
            <div class="space-y-4">
                @foreach($orders as $order)
                    <x-card class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $order->product?->name_en ?? __('Order') }}</p>
                            <p class="text-sm text-gray-500">{{ __('Reference') }}: <span class="font-mono">{{ $order->uuid }}</span></p>
                            <p class="text-sm text-gray-600">{{ __('Ordered') }}: {{ $order->ordered_at?->format('d M Y H:i') }} · {{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <x-badge :variant="$order->payment_status === 'verified' ? 'success' : 'warning'">{{ $order->payment_status === 'verified' ? __('Payment verified') : __('Payment pending') }}</x-badge>
                                <x-badge variant="default">{{ ucfirst($order->order_status ?? 'pending') }}</x-badge>
                            </div>
                        </div>
                        <a href="{{ route('order.confirm', ['uuid' => $order->uuid]) }}" class="inline-flex shrink-0 items-center rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700">
                            {{ __('View order') }}
                        </a>
                    </x-card>
                @endforeach
            </div>
        @endif
    @endif

    <p class="mt-8 text-center text-sm text-gray-500">
        <a href="{{ route('home') }}" class="text-gray-600 hover:underline">{{ __('Back to home') }}</a>
    </p>
</div>
@endsection
