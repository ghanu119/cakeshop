@props(['type'])

<div>
    <label for="slug" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Slug') }}</label>
    <x-input type="text" name="slug" id="slug" value="{{ old('slug', $type?->slug) }}" required class="block w-full" placeholder="weight" />
    @error('slug')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="name_en" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
    <x-input type="text" name="name_en" id="name_en" value="{{ old('name_en', $type?->name_en) }}" required class="block w-full" />
    @error('name_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="selection_mode" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Selection mode') }}</label>
    <select name="selection_mode" id="selection_mode" class="block w-full rounded-lg border border-gray-300 px-3 py-2">
        <option value="single" @selected(old('selection_mode', $type?->selection_mode) === 'single')>{{ __('Single') }}</option>
        <option value="multiple" @selected(old('selection_mode', $type?->selection_mode) === 'multiple')>{{ __('Multiple') }}</option>
    </select>
    @error('selection_mode')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2">
        <option value="active" @selected(old('status', $type?->status) === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $type?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
</div>
<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $type?->sort_order ?? 0) }}" min="0" class="block w-full" />
</div>
