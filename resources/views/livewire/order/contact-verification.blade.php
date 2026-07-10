<div class="space-y-4" data-order-contact-section>
    <div class="mb-2">
        <h3 class="text-lg font-bold text-stone-900">{{ __('Who is this order for?') }}</h3>
        @if(whatsapp_login_enabled())
            <p class="mt-1 text-sm text-stone-600">{{ __('Enter contact details for this order. You will verify your WhatsApp number or email when you place the order.') }}</p>
        @else
            <p class="mt-1 text-sm text-stone-600">{{ __('Enter contact details for this order. You will verify your email when you place the order.') }}</p>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
        <div>
            <label for="guest_name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Your name') }} <span class="text-red-500">*</span></label>
            <input type="text" name="guest_name" id="guest_name" wire:model="guest_name" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required />
            @error('guest_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="guest_phone" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Phone') }} <span class="text-red-500">*</span></label>
            <input type="tel" name="guest_phone" id="guest_phone" wire:model="guest_phone" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" required />
            @error('guest_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="guest_email" class="mb-1 block text-sm font-medium text-gray-700">
                {{ __('Email') }}
                @unless(whatsapp_login_enabled())
                    <span class="text-red-500">*</span>
                @else
                    <span class="font-normal text-stone-500">({{ __('optional') }})</span>
                @endunless
            </label>
            <input type="email" name="guest_email" id="guest_email" wire:model="guest_email" class="block w-full rounded-lg border-stone-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" @unless(whatsapp_login_enabled()) required @endunless />
            @error('guest_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
