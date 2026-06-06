@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-md px-4 py-8 sm:px-6 lg:px-8">
    <h1 class="mb-6 text-2xl font-bold tracking-tight text-gray-900">{{ __('Submit payment details') }}</h1>

    <x-card>
        <p class="mb-4 text-gray-600">{{ __('Enter your order reference (UUID) and phone number to continue.') }}</p>
        <form method="post" action="{{ route('order.submit-payment.lookup') }}" class="space-y-4">
            @csrf
            <div>
                <label for="order_no" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Order reference') }} *</label>
                <x-input type="text" name="order_no" id="order_no" value="{{ old('order_no') }}" class="block w-full" placeholder="e.g. BB-20260606-001" required />
                @error('order_no')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone number') }} *</label>
                <x-input type="text" name="phone" id="phone" value="{{ old('phone') }}" class="block w-full" required />
                @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <x-button type="submit" variant="primary">{{ __('Continue') }}</x-button>
        </form>
    </x-card>
</div>
@endsection
