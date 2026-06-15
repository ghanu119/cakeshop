@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ __('Step 2 of 3') }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ __('Enter your code') }}</h1>
        <p class="mt-2 text-stone-600">{{ __('We sent a 6-digit code to :email', ['email' => $maskedEmail]) }}</p>
    </div>

    <x-card>
        <form method="post" action="{{ route('account.verify-otp.submit') }}" class="space-y-4" data-otp-form>
            @csrf
            <input type="hidden" name="email" value="{{ $email }}" />
            <x-form-errors show-system-errors :show-validation-summary="true" />
            <div>
                <label for="code" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Sign-in code') }}</label>
                <x-input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" class="block w-full text-center text-2xl tracking-[0.5em]" required autofocus autocomplete="one-time-code" data-otp-input />
                @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-button type="submit" variant="primary" class="w-full justify-center">{{ __('Verify and continue') }}</x-button>
        </form>
        <form method="post" action="{{ route('account.login.send-otp') }}" class="mt-4 text-center">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}" />
            <button type="submit" class="text-sm text-amber-700 hover:underline">{{ __('Resend code') }}</button>
        </form>
        <p class="mt-4 text-center text-sm text-stone-500">
            <a href="{{ route('account.login') }}" class="hover:underline">{{ __('Use a different email') }}</a>
        </p>
    </x-card>
</div>
@endsection

@push('scripts')
    @vite(['resources/js/account-otp.js'])
@endpush
