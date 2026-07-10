<x-modal name="customer-auth-modal" focusable maxWidth="md">
    <div class="p-6">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div>
                @if($step === 'contact')
                    <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ __('Sign in') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-stone-900">{{ __('Sign in to your account') }}</h2>
                    <p class="mt-1 text-sm text-stone-600">{{ __('We\'ll send you a one-time code to sign in.') }}</p>
                @elseif($step === 'otp')
                    <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ $channel === 'whatsapp' ? __('Verify WhatsApp') : __('Verify email') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-stone-900">{{ __('Enter your code') }}</h2>
                    @if($channel === 'whatsapp')
                        <p class="mt-1 text-sm text-stone-600">{{ __('We sent a code on WhatsApp to :phone', ['phone' => $maskedPhone]) }}</p>
                    @else
                        <p class="mt-1 text-sm text-stone-600">{{ __('We sent a code to :email', ['email' => $maskedEmail]) }}</p>
                    @endif
                @else
                    <p class="text-sm font-medium uppercase tracking-wider text-amber-600">{{ __('Complete profile') }}</p>
                    <h2 class="mt-1 text-xl font-bold text-stone-900">{{ __('Almost done') }}</h2>
                    @if($channel === 'whatsapp')
                        <p class="mt-1 text-sm text-stone-600">{{ __('Tell us your name to finish signing up. Email is optional.') }}</p>
                    @else
                        <p class="mt-1 text-sm text-stone-600">{{ __('Tell us your name and phone number.') }}</p>
                    @endif
                @endif
            </div>
            <button type="button" wire:click="close" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600" aria-label="{{ __('Close') }}">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        @if($statusMessage)
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                {{ $statusMessage }}
            </div>
        @endif

        @if($step === 'contact')
            <form wire:submit="sendOtp" class="space-y-4">
                @if($channel === 'whatsapp')
                    <div>
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-x-2 gap-y-1">
                            <label for="auth-modal-phone-contact" class="text-sm font-medium text-stone-700">{{ __('Mobile number') }}</label>
                            <button type="button" wire:click="switchChannel('email')" class="text-sm text-amber-700 hover:underline">({{ __('Use Email') }})</button>
                        </div>
                        <input type="tel" id="auth-modal-phone-contact" wire:model="phone" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" minlength="10" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required autofocus autocomplete="tel" placeholder="{{ __('e.g. 9876543210') }}" />
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <x-button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendOtp">{{ __('Send WhatsApp code') }}</span>
                        <span wire:loading wire:target="sendOtp">{{ __('Sending...') }}</span>
                    </x-button>
                @else
                    <div>
                        <div class="mb-1 flex flex-wrap items-baseline justify-between gap-x-2 gap-y-1">
                            <label for="auth-modal-email" class="text-sm font-medium text-stone-700">{{ __('Email address') }}</label>
                            @if($whatsappEnabled)
                                <button type="button" wire:click="switchChannel('whatsapp')" class="text-sm text-amber-700 hover:underline">({{ __('Use Mobile') }})</button>
                            @endif
                        </div>
                        <input type="email" id="auth-modal-email" wire:model="email" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required autofocus autocomplete="email" inputmode="email" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <x-button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="sendOtp">{{ __('Continue') }}</span>
                        <span wire:loading wire:target="sendOtp">{{ __('Sending...') }}</span>
                    </x-button>
                @endif
            </form>
        @elseif($step === 'otp')
            <form wire:submit="verifyOtp" class="space-y-4">
                <div>
                    <label for="auth-modal-code" class="mb-1 block text-sm font-medium text-stone-700">{{ __('6-digit code') }}</label>
                    <input type="text" id="auth-modal-code" wire:model="code" inputmode="numeric" pattern="[0-9]*" maxlength="6" class="block w-full rounded-lg border-stone-300 text-center text-2xl tracking-[0.3em] shadow-sm focus:border-amber-500 focus:ring-amber-500" required autofocus autocomplete="one-time-code" />
                    @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <x-button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="verifyOtp">{{ __('Verify and continue') }}</span>
                    <span wire:loading wire:target="verifyOtp">{{ __('Verifying...') }}</span>
                </x-button>
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <button type="button" wire:click="goBackToContact" class="text-stone-600 hover:text-stone-900">{{ $channel === 'whatsapp' ? __('Use a different number') : __('Use a different email') }}</button>
                    <button type="button" wire:click="resendOtp" class="text-amber-700 hover:underline" wire:loading.attr="disabled">{{ __('Resend code') }}</button>
                </div>
                @if($channel === 'whatsapp')
                    <button type="button" wire:click="switchChannel('email')" class="w-full text-center text-sm text-amber-700 hover:underline">{{ __('Didn\'t get it? Use email instead') }}</button>
                @endif
            </form>
        @else
            @if($phoneOnlyPreview)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <h3 class="font-semibold text-amber-900">{{ __('We found your store account') }}</h3>
                    <p class="mt-1 text-sm text-amber-800">
                        {{ __('Phone') }}: {{ $phoneOnlyPreview['phone_masked'] }}<br>
                        {{ __('We will link :email to this account.', ['email' => $maskedEmail]) }}
                    </p>
                </div>
            @endif
            <form wire:submit="completeProfile" class="space-y-4">
                <div>
                    <label for="auth-modal-name" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Your name') }}</label>
                    <input type="text" id="auth-modal-name" wire:model="name" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required />
                    @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                @if($channel === 'whatsapp')
                    <div>
                        <label for="auth-modal-email-required" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Email address') }} <span class="font-normal text-stone-500">({{ __('optional') }})</span></label>
                        <input type="email" id="auth-modal-email-required" wire:model="email" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" autocomplete="email" inputmode="email" />
                        @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-stone-500">{{ __('Your WhatsApp number :phone will be linked to your account.', ['phone' => $maskedPhone]) }}</p>
                @else
                    <div>
                        <label for="auth-modal-phone" class="mb-1 block text-sm font-medium text-stone-700">{{ __('Phone number') }}</label>
                        <input type="tel" id="auth-modal-phone" wire:model.live.debounce.400ms="phone" inputmode="numeric" pattern="[6-9][0-9]{9}" maxlength="10" minlength="10" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required autocomplete="tel" placeholder="{{ __('e.g. 9876543210') }}" />
                        @error('phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <p class="text-xs text-stone-500">{{ __('Email :email will be linked to your account.', ['email' => $maskedEmail]) }}</p>
                @endif
                <x-button type="submit" variant="primary" class="w-full justify-center" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="completeProfile">{{ $phoneOnlyPreview ? __('Complete signup') : __('Create account') }}</span>
                    <span wire:loading wire:target="completeProfile">{{ __('Creating account...') }}</span>
                </x-button>
                <button type="button" wire:click="goBackToOtp" class="w-full text-center text-sm text-stone-600 hover:text-stone-900">{{ __('Back') }}</button>
            </form>
        @endif

        <p class="mt-6 text-center text-sm text-stone-500">
            <a href="{{ route('order.history') }}" class="text-amber-700 hover:underline">{{ __('Look up an order without signing in') }}</a>
        </p>
    </div>
</x-modal>
