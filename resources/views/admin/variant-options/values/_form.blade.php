@props(['type', 'value'])

<div>
    <label for="label" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Label') }}</label>
    <x-input type="text" name="label" id="label" value="{{ old('label', $value?->label) }}" required class="block w-full" placeholder="{{ $type->slug === 'weight' ? '500 gm' : '' }}" />
    @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
@if($type->slug === 'weight')
<div>
    <label for="grams" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Grams (for sorting & filters)') }}</label>
    <x-input type="number" name="grams" id="grams" value="{{ old('grams', $value?->grams) }}" min="1" class="block w-full" />
    @error('grams')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
@endif
<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2">
        <option value="active" @selected(old('status', $value?->status) === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $value?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
</div>
<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $value?->sort_order ?? 0) }}" min="0" class="block w-full" />
</div>
