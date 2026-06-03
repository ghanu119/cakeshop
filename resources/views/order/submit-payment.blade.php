@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-2xl px-4 py-8 sm:px-6 lg:px-8">
        <h1 class="mb-6 text-2xl font-bold tracking-tight text-gray-900">{{ __('Submit payment details') }}</h1>

        <x-card>
            <p class="mb-4 text-gray-600">{{ __('Order') }}: <span class="font-mono">{{ $order->uuid }}</span></p>
            <div class="mb-4 rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-700">
                <p class="font-medium text-gray-900">{{ $order->displayProductName() }}</p>
                @include('order.partials._order-options', ['order' => $order])
                <p class="mt-1">{{ __('Amount') }}: ₹ {{ number_format($order->amount, 2) }}</p>
            </div>
            <form method="post" action="{{ route('order.submit-payment.store', ['uuid' => $order->uuid]) }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="phone" value="{{ $order->guest_phone }}" />
                <div>
                    <label for="payment_reference" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Transaction / UPI reference') }}</label>
                    <x-input type="text" name="payment_reference" id="payment_reference" value="{{ old('payment_reference') }}" class="block w-full" />
                    @error('payment_reference')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="payment_amount" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Amount paid') }}</label>
                    <x-input type="number" name="payment_amount" id="payment_amount" value="{{ old('payment_amount', $order->amount) }}" step="0.01" min="0" class="block w-full" />
                    @error('payment_amount')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="payment_made_at" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Date & time of payment') }}</label>
                    <x-input type="datetime-local" name="payment_made_at" id="payment_made_at" value="{{ old('payment_made_at') }}" class="block w-full" />
                    @error('payment_made_at')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="payment_proof" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment proof (screenshot)') }}</label>
                    <input type="file" name="payment_proof" id="payment_proof" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700 file:hover:bg-gray-200" />
                    @error('payment_proof')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <x-button type="submit" variant="primary">{{ __('Submit') }}</x-button>
            </form>
        </x-card>
    </div>
@endsection
