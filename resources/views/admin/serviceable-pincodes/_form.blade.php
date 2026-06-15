@props(['pincode'])

<div class="space-y-5">
    <div>
        <label for="pincode" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Pincode') }} <span class="text-red-500">*</span></label>
        <input
            type="text"
            name="pincode"
            id="pincode"
            value="{{ old('pincode', $pincode?->pincode) }}"
            required
            inputmode="numeric"
            pattern="[0-9]{6}"
            maxlength="6"
            autocomplete="off"
            placeholder="360001"
            class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('6-digit Indian pincode. Only active pincodes allow delivery checkout.') }}</p>
        @error('pincode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="locality" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Locality / area name') }}</label>
        <input
            type="text"
            name="locality"
            id="locality"
            value="{{ old('locality', $pincode?->locality) }}"
            autocomplete="off"
            placeholder="{{ __('e.g. Kalawad Road') }}"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('Shown to customers when their pincode is validated.') }}</p>
        @error('locality')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="city" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('City') }} <span class="text-red-500">*</span></label>
            <input
                type="text"
                name="city"
                id="city"
                value="{{ old('city', $pincode?->city ?? 'Rajkot') }}"
                required
                autocomplete="off"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
            @error('city')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="state" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('State') }} <span class="text-red-500">*</span></label>
            <input
                type="text"
                name="state"
                id="state"
                value="{{ old('state', $pincode?->state ?? 'Gujarat') }}"
                required
                autocomplete="off"
                class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
            @error('state')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div>
        <label class="mb-2 flex items-center gap-2">
            <input
                type="hidden"
                name="is_active"
                value="0"
            />
            <input
                type="checkbox"
                name="is_active"
                id="is_active"
                value="1"
                @checked(old('is_active', $pincode?->is_active ?? true))
                class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <span class="text-sm font-medium text-gray-700">{{ __('Active (allow delivery to this pincode)') }}</span>
        </label>
        @error('is_active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
