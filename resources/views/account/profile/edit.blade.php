@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="mb-6">
        <a href="{{ route('account.dashboard') }}" class="text-sm text-stone-600 hover:underline">{{ __('← Account') }}</a>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ __('Your profile') }}</h1>
        <p class="mt-1 text-stone-600">{{ __('Update your personal details.') }}</p>
    </header>

    <x-card class="mb-6">
        <form method="post" action="{{ route('account.profile.update') }}" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form-errors show-system-errors :show-validation-summary="true" />
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Name') }}</label>
                <x-input type="text" name="name" id="name" value="{{ old('name', $customer->name) }}" class="block w-full" required />
                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('Email') }}</label>
                <x-input type="email" value="{{ $customer->email ?? __('Not added yet') }}" class="block w-full bg-stone-50" disabled />
                <p class="mt-1 text-xs text-stone-500">{{ __('Contact the store to update your email.') }}</p>
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">{{ __('Phone') }}</label>
                <x-input type="text" value="{{ $customer->phone }}" class="block w-full bg-stone-50" disabled />
                <p class="mt-1 text-xs text-stone-500">{{ __('Contact the store to update your phone number.') }}</p>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="birth_month" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Birth month') }}</label>
                    <select name="birth_month" id="birth_month" class="block w-full rounded-lg border border-stone-300 px-3 py-2">
                        <option value="">{{ __('—') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) old('birth_month', $customer->birth_month) === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="birth_day" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Birth day') }}</label>
                    <select name="birth_day" id="birth_day" class="block w-full rounded-lg border border-stone-300 px-3 py-2">
                        <option value="">{{ __('—') }}</option>
                        @for($d = 1; $d <= 31; $d++)
                            <option value="{{ $d }}" @selected((int) old('birth_day', $customer->birth_day) === $d)>{{ $d }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="anniversary_month" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Anniversary month') }}</label>
                    <select name="anniversary_month" id="anniversary_month" class="block w-full rounded-lg border border-stone-300 px-3 py-2">
                        <option value="">{{ __('—') }}</option>
                        @for($m = 1; $m <= 12; $m++)
                            <option value="{{ $m }}" @selected((int) old('anniversary_month', $customer->anniversary_month) === $m)>{{ date('F', mktime(0, 0, 0, $m, 1)) }}</option>
                        @endfor
                    </select>
                </div>
                <div>
                    <label for="anniversary_day" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Anniversary day') }}</label>
                    <select name="anniversary_day" id="anniversary_day" class="block w-full rounded-lg border border-stone-300 px-3 py-2">
                        <option value="">{{ __('—') }}</option>
                        @for($d = 1; $d <= 31; $d++)
                            <option value="{{ $d }}" @selected((int) old('anniversary_day', $customer->anniversary_day) === $d)>{{ $d }}</option>
                        @endfor
                    </select>
                </div>
            </div>
            <div>
                <label for="gender" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Gender') }}</label>
                <select name="gender" id="gender" class="block w-full rounded-lg border border-stone-300 px-3 py-2">
                    <option value="">{{ __('—') }}</option>
                    @foreach($genders as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $customer->gender) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <x-button type="submit" variant="primary">{{ __('Save changes') }}</x-button>
                <a
                    href="{{ route('account.dashboard') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                >
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>

    <x-card class="border-red-200">
        <h2 class="text-lg font-semibold text-red-900">{{ __('Delete my account') }}</h2>
        <p class="mt-2 text-sm text-stone-600">{{ __('Orders on this account will no longer be accessible to you. This cannot be undone from the website.') }}</p>
        <form method="post" action="{{ route('account.profile.destroy') }}" class="mt-4" onsubmit="return confirm(@json(__('Are you sure you want to delete your account?')));">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger">{{ __('Delete my account') }}</x-button>
        </form>
    </x-card>
</div>
@endsection
