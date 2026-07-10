@props([
    'customer' => null,
    'variant' => 'default',
])

@php
    $contactName = old('guest_name', $customer?->name ?? '');
    $contactPhone = old('guest_phone', $customer?->phone ?? '');
    $contactEmail = old('guest_email', $customer?->email ?? '');
    $isImpersonating = app(\App\Services\CustomerContext::class)->isImpersonating();
    $emailRequired = ! $isImpersonating && ! whatsapp_login_enabled();
    $inputClass = $variant === 'checkout'
        ? 'w-full rounded-xl border border-stone-200 bg-stone-50/50 px-4 py-3 text-stone-900 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 transition-all shadow-sm'
        : 'block w-full';
    $labelClass = $variant === 'checkout'
        ? 'mb-2 block text-sm font-bold text-stone-700'
        : 'mb-1 block text-sm font-medium text-gray-700';
    $errorClass = $variant === 'checkout'
        ? 'mt-2 text-sm text-red-600 font-medium flex items-center gap-1'
        : 'mt-1 text-sm text-red-600';
@endphp

<div {{ $attributes->merge(['class' => $variant === 'checkout' ? 'mb-0' : 'space-y-4', 'data-order-contact-section' => true]) }}>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3 @if($variant === 'checkout') border-b border-stone-100 pb-4 @endif">
        <div>
            @if($variant === 'checkout')
                <h3 class="text-xl font-bold text-stone-900 flex items-center gap-3 whitespace-nowrap">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-500 text-white text-sm font-bold shadow-sm">1</span>
                    {{ __('Who is this order for?') }}
                </h3>
            @else
                <h3 class="text-lg font-bold text-stone-900">{{ __('Who is this order for?') }}</h3>
            @endif
            @if($customer && $isImpersonating)
                <p class="mt-1 text-sm text-amber-800">
                    @if($customer->hasEmail())
                        {{ __('Prefilled from this customer account. Change if ordering for someone else.') }}
                    @else
                        {{ __('Prefilled from this customer account. Email is optional when none is on file.') }}
                    @endif
                </p>
            @elseif($customer)
                <p class="mt-1 text-sm text-amber-800">{{ __('Prefilled from your account. Change if ordering for someone else.') }}</p>
            @endif
        </div>
        @if($customer)
            <button
                type="button"
                data-clear-order-contact
                class="inline-flex items-center rounded-lg border border-stone-300 bg-white px-3 py-1.5 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
            >
                {{ __('Clear') }}
            </button>
        @endif
    </div>

    <div @class(['grid grid-cols-1 md:grid-cols-2 gap-6' => $variant === 'checkout', 'space-y-4' => $variant !== 'checkout'])>
        <div>
            <label for="guest_name" class="{{ $labelClass }}">
                {{ __('Your name') }}
                <span class="text-red-500">*</span>
            </label>
            @if($variant === 'checkout')
                <input type="text" name="guest_name" id="guest_name" value="{{ $contactName }}" class="{{ $inputClass }}" required />
            @else
                <x-input type="text" name="guest_name" id="guest_name" value="{{ $contactName }}" :class="$inputClass" required />
            @endif
            @error('guest_name')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="guest_phone" class="{{ $labelClass }}">
                {{ __('Phone') }}
                <span class="text-red-500">*</span>
            </label>
            @if($variant === 'checkout')
                <input type="tel" name="guest_phone" id="guest_phone" value="{{ $contactPhone }}" class="{{ $inputClass }}" required />
            @else
                <x-input type="text" name="guest_phone" id="guest_phone" value="{{ $contactPhone }}" :class="$inputClass" required />
            @endif
            @error('guest_phone')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>

        <div @if($variant === 'checkout') class="md:col-span-2" @endif>
            <label for="guest_email" class="{{ $labelClass }}">
                {{ __('Email') }}
                @if($emailRequired)
                    <span class="text-red-500">*</span>
                @else
                    <span class="text-xs font-normal text-stone-500">({{ __('Optional') }})</span>
                @endif
            </label>
            @if($variant === 'checkout')
                <input type="email" name="guest_email" id="guest_email" value="{{ $contactEmail }}" class="{{ $inputClass }}" @if($emailRequired) required @endif />
            @else
                <x-input type="email" name="guest_email" id="guest_email" value="{{ $contactEmail }}" :class="$inputClass" :required="$emailRequired" />
            @endif
            @error('guest_email')<p class="{{ $errorClass }}">{{ $message }}</p>@enderror
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-clear-order-contact]').forEach((button) => {
                    button.addEventListener('click', () => {
                        ['guest_name', 'guest_phone', 'guest_email'].forEach((id) => {
                            const field = document.getElementById(id);
                            if (field) {
                                field.value = '';
                            }
                        });
                        button.closest('[data-order-contact-section]')?.querySelector('#guest_name')?.focus();
                    });
                });
            });
        </script>
    @endpush
@endonce
