@props(['category'])

<div>
    <label for="name_en" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (English)') }}</label>
    <x-input type="text" name="name_en" id="name_en" value="{{ old('name_en', $category?->name_en) }}" required class="block w-full" />
    @error('name_en')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name_hi" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (Hindi)') }}</label>
    <x-input type="text" name="name_hi" id="name_hi" value="{{ old('name_hi', $category?->name_hi) }}" class="block w-full" />
    @error('name_hi')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name_gu" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name (Gujarati)') }}</label>
    <x-input type="text" name="name_gu" id="name_gu" value="{{ old('name_gu', $category?->name_gu) }}" class="block w-full" />
    @error('name_gu')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
        <option value="active" @selected(old('status', $category?->status) === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $category?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
    @error('status')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $category?->sort_order ?? 0) }}" min="0" class="block w-full" />
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
