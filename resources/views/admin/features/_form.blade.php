@props(['feature'])

<div>
    <label for="title" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
    <x-input type="text" name="title" id="title" value="{{ old('title', $feature?->title) }}" required class="block w-full" />
    @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('description', $feature?->description) }}</textarea>
    @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="icon" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Icon (name or class)') }}</label>
    <x-input type="text" name="icon" id="icon" value="{{ old('icon', $feature?->icon) }}" class="block w-full" placeholder="e.g. shopping-cart" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Optional. Icon name for UI or leave blank if using icon file.') }}</p>
    @error('icon')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="icon_file" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Icon file') }}</label>
    <input type="file" name="icon_file" id="icon_file" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" />
    @if($feature?->icon && \Illuminate\Support\Str::startsWith($feature->icon, 'features/'))
        <p class="mt-1 text-xs text-gray-600">{{ __('Current:') }} <a href="{{ asset('storage/' . $feature->icon) }}" target="_blank" class="text-indigo-600 hover:underline">{{ $feature->icon }}</a></p>
    @endif
    @error('icon_file')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $feature?->sort_order ?? 0) }}" min="0" class="block w-full" />
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="inline-flex items-center gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $feature?->is_active ?? true)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Active') }}</span>
    </label>
    @error('is_active')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
