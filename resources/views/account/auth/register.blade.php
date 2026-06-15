@extends('layouts.app')

@section('content')
@php
    $phoneOnly = null;
    if (old('phone')) {
        $phoneOnly = app(\App\Services\CustomerAuthService::class)->findPhoneOnlyCustomer(old('phone'));
    }
@endphp
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ __('Step 3 of 3') }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ __('Complete your profile') }}</h1>
        <p class="mt-2 text-stone-600">{{ __('Almost done — tell us your name and phone number.') }}</p>
    </div>

    <x-card>
        @if($phoneOnly)
            <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-4">
                <h2 class="font-semibold text-amber-900">{{ __('We found your store account') }}</h2>
                <p class="mt-1 text-sm text-amber-800">
                    {{ __('Phone') }}: {{ \App\Support\PhoneNormalizer::mask($phoneOnly->phone) }}<br>
                    {{ __('We will link :email to this account. Your past orders will show up after you continue.', ['email' => $maskedEmail]) }}
                </p>
            </div>
        @endif
        <form method="post" action="{{ route('account.register.submit') }}" class="space-y-4" data-register-form data-check-phone-url="{{ route('account.register.check-phone') }}">
            @csrf
            <x-form-errors show-system-errors :show-validation-summary="true" />
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Your name') }}</label>
                <x-input type="text" name="name" id="name" value="{{ old('name', $phoneOnly?->name) }}" class="block w-full" required />
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Phone number') }}</label>
                <x-input type="tel" name="phone" id="phone" value="{{ old('phone') }}" class="block w-full" required data-phone-input />
                @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                <div id="phone-claim-preview" class="mt-3 hidden rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-900"></div>
            </div>
            <p class="text-xs text-stone-500">{{ __('Email :email will be linked to your account.', ['email' => $maskedEmail]) }}</p>
            <x-button type="submit" variant="primary" class="w-full justify-center">
                {{ $phoneOnly ? __('Complete signup') : __('Create account') }}
            </x-button>
        </form>
    </x-card>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/account-register.js'])
@endpush
