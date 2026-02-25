@props(['testimonial'])

<div>
    <label for="customer_name" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Customer name') }}</label>
    <x-input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', $testimonial?->customer_name) }}" required class="block w-full" />
    @error('customer_name')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="customer_initials" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Initials (optional)') }}</label>
    <x-input type="text" name="customer_initials" id="customer_initials" value="{{ old('customer_initials', $testimonial?->customer_initials) }}" class="block w-full" maxlength="10" />
    @error('customer_initials')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="review" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Review') }}</label>
    <textarea name="review" id="review" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" required>{{ old('review', $testimonial?->review) }}</textarea>
    @error('review')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="rating" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Rating (1-5)') }}</label>
    <select name="rating" id="rating" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2" required>
        @foreach(range(1, 5) as $r)
            <option value="{{ $r }}" @selected(old('rating', $testimonial?->rating ?? 5) == $r)>{{ $r }}</option>
        @endforeach
    </select>
    @error('rating')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $testimonial?->sort_order ?? 0) }}" min="0" class="block w-full" />
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label class="inline-flex items-center gap-2">
        <input type="hidden" name="is_verified" value="0" />
        <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $testimonial?->is_verified ?? false)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Verified purchase') }}</span>
    </label>
    <label class="ml-6 inline-flex items-center gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $testimonial?->is_active ?? true)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Active') }}</span>
    </label>
</div>
