@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-md px-4 py-12 sm:px-6 lg:px-8">
    <div class="mb-8 text-center">
        <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ __('Step 1 of 3') }}</p>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ __('Sign in to your account') }}</h1>
        <p class="mt-2 text-stone-600">{{ __('Enter your email and we will send you a one-time code.') }}</p>
    </div>

    <x-card>
        <form method="post" action="{{ route('account.login.send-otp') }}" class="space-y-4">
            @csrf
            @if($intended ?? null)
                <input type="hidden" name="intended" value="{{ $intended }}" />
            @endif
            <x-form-errors show-system-errors :show-validation-summary="true" />
            <div>
                <label for="email" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Email address') }}</label>
                <x-input type="email" name="email" id="email" value="{{ old('email') }}" class="block w-full text-lg" required autofocus autocomplete="email" />
                @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <x-button type="submit" variant="primary" class="w-full justify-center">{{ __('Continue') }}</x-button>
        </form>
        <p class="mt-6 text-center text-sm text-stone-500">
            <a href="{{ route('order.history') }}" class="text-amber-700 hover:underline">{{ __('Look up an order without signing in') }}</a>
        </p>
    </x-card>
</div>
@endsection
