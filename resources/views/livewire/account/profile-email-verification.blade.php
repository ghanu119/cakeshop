<div class="space-y-3">
    @if($statusMessage)
        <div class="rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900">
            {{ $statusMessage }}
        </div>
    @endif

    @if($step === 'edit')
        <div>
            <label for="profile-email" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Email') }}</label>
            <input
                type="email"
                id="profile-email"
                wire:model="email"
                class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
                placeholder="{{ $currentEmail === '' ? __('Enter your email address') : '' }}"
                autocomplete="email"
                inputmode="email"
            />
            @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @if($currentEmail !== '' && strtolower(trim($email)) === $currentEmail)
                <p class="mt-1 text-xs text-stone-500">{{ __('This is your current email. Enter a new address to change it.') }}</p>
            @else
                <p class="mt-1 text-xs text-stone-500">{{ __('We will send a verification code to confirm your email.') }}</p>
            @endif
        </div>

        @if($currentEmail === '' || strtolower(trim($email)) !== $currentEmail)
            <x-button type="button" variant="secondary" wire:click="sendOtp" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="sendOtp">{{ __('Send verification code') }}</span>
                <span wire:loading wire:target="sendOtp">{{ __('Sending...') }}</span>
            </x-button>
        @endif
    @else
        <div>
            <p class="text-sm text-stone-600">{{ __('We sent a code to :email', ['email' => $maskedEmail]) }}</p>
            <label for="profile-email-code" class="mb-1 mt-3 block text-sm font-medium text-stone-700">{{ __('6-digit code') }}</label>
            <input
                type="text"
                id="profile-email-code"
                wire:model="code"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="6"
                class="block w-full rounded-lg border-stone-300 text-center text-xl tracking-[0.3em] shadow-sm focus:border-amber-500 focus:ring-amber-500"
                autocomplete="one-time-code"
                data-otp-input
            />
            @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <x-button type="button" variant="primary" wire:click="verifyOtp" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="verifyOtp">{{ __('Verify email') }}</span>
                <span wire:loading wire:target="verifyOtp">{{ __('Verifying...') }}</span>
            </x-button>
            <button type="button" wire:click="resendOtp" class="text-sm text-amber-700 hover:underline" wire:loading.attr="disabled">
                {{ __('Resend code') }}
            </button>
            <button type="button" wire:click="cancelOtp" class="text-sm text-stone-600 hover:text-stone-900">
                {{ __('Cancel') }}
            </button>
        </div>
    @endif
</div>
