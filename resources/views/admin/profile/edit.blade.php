@extends('layouts.admin')

@section('title', __('Profile'))

@section('content')
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Profile') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Update your account details') }}</p>
    </header>

    <div class="max-w-xl space-y-6">
        <x-card>
            <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Profile information') }}</h2>
            <form method="post" action="{{ route('admin.profile.update') }}" class="space-y-4">
                @csrf
            <x-form-errors :show-validation-summary="true" />
                @method('PUT')
                <div>
                    <label for="name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
                    <x-input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required class="block w-full" />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Email') }}</label>
                    <x-input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required class="block w-full" />
                    @error('email')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone') }}</label>
                    <x-input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="block w-full" />
                    @error('phone')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <x-button type="submit" variant="primary">{{ __('Save profile') }}</x-button>
            </form>
        </x-card>

        <x-card>
            <h2 class="mb-4 text-lg font-semibold text-gray-900">{{ __('Change password') }}</h2>
            <form method="post" action="{{ route('admin.profile.password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label for="current_password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Current password') }}</label>
                    <x-input type="password" name="current_password" id="current_password" class="block w-full" required />
                    @error('current_password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-gray-700">{{ __('New password') }}</label>
                    <x-input type="password" name="password" id="password" class="block w-full" required />
                    @error('password')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Confirm new password') }}</label>
                    <x-input type="password" name="password_confirmation" id="password_confirmation" class="block w-full" required />
                </div>
                <x-button type="submit" variant="primary">{{ __('Update password') }}</x-button>
            </form>
        </x-card>
    </div>
@endsection
