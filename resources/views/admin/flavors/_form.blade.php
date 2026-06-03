@props(['flavor'])

<div>
    <label for="name_en" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (English)') }}</label>
    <x-input type="text" name="name_en" id="name_en" value="{{ old('name_en', $flavor?->name_en) }}" required class="block w-full" />
    @error('name_en')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name_hi" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (Hindi)') }}</label>
    <x-input type="text" name="name_hi" id="name_hi" value="{{ old('name_hi', $flavor?->name_hi) }}" class="block w-full" />
    @error('name_hi')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name_gu" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (Gujarati)') }}</label>
    <x-input type="text" name="name_gu" id="name_gu" value="{{ old('name_gu', $flavor?->name_gu) }}" class="block w-full" />
    @error('name_gu')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
        <option value="active" @selected(old('status', $flavor?->status) === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $flavor?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
    @error('status')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $flavor?->sort_order ?? 0) }}" min="0" class="block w-full" />
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="badge_color" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Badge color') }}</label>
    <x-input type="text" name="badge_color" id="badge_color" value="{{ old('badge_color', $flavor?->badge_color) }}" placeholder="rose, amber, stone…" class="block w-full" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Optional Tailwind color name for storefront badges.') }}</p>
    @error('badge_color')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
