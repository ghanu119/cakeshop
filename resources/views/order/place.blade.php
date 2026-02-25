@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-3xl font-bold tracking-tight text-gray-900">{{ __('Order') }}: {{ $product->name_en }}</h1>
    <x-card class="mb-6">
        <p class="mb-4 text-gray-600">Rs. {{ number_format($product->price, 2) }} {{ __('per piece') }}</p>
        <form method="post" action="{{ route('order.store', $product) }}" class="space-y-4">
            @csrf
            <div>
                <label for="guest_name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Your name') }} *</label>
                <x-input type="text" name="guest_name" id="guest_name" value="{{ old('guest_name') }}" class="block w-full" required />
                @error('guest_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="guest_phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone') }} *</label>
                <x-input type="text" name="guest_phone" id="guest_phone" value="{{ old('guest_phone') }}" class="block w-full" required />
                @error('guest_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="guest_email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                <x-input type="email" name="guest_email" id="guest_email" value="{{ old('guest_email') }}" class="block w-full" />
                @error('guest_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="quantity" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Quantity') }} *</label>
                <x-input type="number" name="quantity" id="quantity" value="{{ old('quantity', request('quantity', 1)) }}" min="1" max="10" class="block w-full" required />
                @error('quantity')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="delivery_at" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Delivery date and time') }} * ({{ $deliveryRules['timezone'] }})</label>
                @php
                    $minDt = $deliveryRules['after']->setTimezone($deliveryRules['timezone'])->format('Y-m-d\TH:i');
                    $maxDt = $deliveryRules['before']->setTimezone($deliveryRules['timezone'])->format('Y-m-d\TH:i');
                @endphp
                <x-input type="datetime-local" name="delivery_at" id="delivery_at" value="{{ old('delivery_at') }}" min="{{ $minDt }}" max="{{ $maxDt }}" class="block w-full" required />
                @error('delivery_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="message_on_cake" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Message on cake') }}</label>
                <x-input type="text" name="message_on_cake" id="message_on_cake" value="{{ old('message_on_cake') }}" class="block w-full" />
                @error('message_on_cake')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="instructions" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Instructions') }}</label>
                <textarea name="instructions" id="instructions" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('instructions') }}</textarea>
                @error('instructions')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-button type="submit" variant="primary" data-submitting-text="{{ __('Processing...') }}">{{ __('Place order') }}</x-button>
        </form>
    </x-card>
</div>
@endsection
