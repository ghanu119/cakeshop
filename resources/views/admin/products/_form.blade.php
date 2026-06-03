@props(['product', 'categories', 'weightValues' => collect(), 'flavors' => collect()])

@php
    $selectedFlavorIds = old('flavor_ids', $product?->flavors?->pluck('id')->all() ?? []);
@endphp

<div>
    <label for="category_id" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Category') }}</label>
    <select name="category_id" id="category_id" class="block w-full rounded-lg border px-3 py-2 shadow-sm focus:ring-2 focus:ring-offset-2 {{ $errors->has('category_id') ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-gray-500 focus:ring-gray-500' }}">
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(old('category_id', $product?->category_id) == $cat->id)>{{ $cat->name_en }}</option>
        @endforeach
    </select>
    @error('category_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="name_en" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Name') }}</label>
    <x-input type="text" name="name_en" id="name_en" value="{{ old('name_en', $product?->name_en) }}" class="block w-full {{ $errors->has('name_en') ? '!border-red-500' : '' }}" />
    @error('name_en')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="short_description" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Short description') }}</label>
    <x-input type="text" name="short_description" id="short_description" value="{{ old('short_description', $product?->short_description) }}" class="block w-full" />
    @error('short_description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="description_en" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Description (English)') }}</label>
    <textarea name="description_en" id="description_en" rows="4" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('description_en', $product?->description_en) }}</textarea>
    @error('description_en')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="ingredients" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Ingredient highlights') }}</label>
    <textarea name="ingredients" id="ingredients" rows="3" placeholder="One per line or comma-separated" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('ingredients', $product?->ingredients) }}</textarea>
    @error('ingredients')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="price" class="mb-1 block text-sm font-medium text-gray-700">
        <span id="price-label-text">{{ __('Price (INR)') }}</span>
        <span id="price-hint-starting" class="hidden text-gray-500 font-normal"> — {{ __('auto-set to lowest variant when variants are added') }}</span>
    </label>
    <x-input type="number" name="price" id="price" value="{{ old('price', $product?->price) }}" step="0.01" min="0" class="block w-full" />
    @error('price')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

@include('admin.products._variants', ['product' => $product, 'weightValues' => $weightValues])

<div>
    <label for="flavor_ids" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Flavors') }}</label>
    <select name="flavor_ids[]" id="flavor_ids" multiple class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
        @foreach($flavors as $flavor)
            <option value="{{ $flavor->id }}" @selected(in_array($flavor->id, $selectedFlavorIds))>{{ $flavor->name_en }}</option>
        @endforeach
    </select>
    <p class="mt-1 text-xs text-gray-500">{{ __('Select flavors customers can choose at checkout. Does not affect price.') }}</p>
    @error('flavor_ids')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('flavor_ids.*')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
        <option value="active" @selected(old('status', $product?->status) === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $product?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
    @error('status')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="space-y-2">
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="show_on_homepage" value="1" @checked(old('show_on_homepage', $product?->show_on_homepage)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Show on homepage') }}</span>
    </label>
    <label class="ml-4 inline-flex items-center gap-2">
        <input type="checkbox" name="is_highlight" value="1" @checked(old('is_highlight', $product?->is_highlight)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Highlight') }}</span>
    </label>
    <label class="ml-4 inline-flex items-center gap-2">
        <input type="checkbox" name="is_trending" value="1" @checked(old('is_trending', $product?->is_trending)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Trending') }}</span>
    </label>
    <label class="ml-4 inline-flex items-center gap-2">
        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product?->is_featured)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Featured') }}</span>
    </label>
</div>

<div>
    <label for="homepage_sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Homepage sort order') }}</label>
    <x-input type="number" name="homepage_sort_order" id="homepage_sort_order" value="{{ old('homepage_sort_order', $product?->homepage_sort_order) }}" min="0" class="block w-full" />
    @error('homepage_sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="meta_title" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Meta title (SEO)') }}</label>
    <x-input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $product?->meta_title) }}" class="block w-full" />
    @error('meta_title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="meta_description" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Meta description (SEO)') }}</label>
    <textarea name="meta_description" id="meta_description" rows="2" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">{{ old('meta_description', $product?->meta_description) }}</textarea>
    @error('meta_description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="image" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Product image') }}</label>
    <input type="file" name="image" id="image" accept="image/*" class="block w-full text-sm text-gray-500 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-gray-700" />
    @if($product?->getFirstMediaUrl('product_images', 'thumb'))
        <p class="mt-1 text-sm text-gray-500">{{ __('Current image:') }} <img src="{{ $product->getFirstMediaUrl('product_images', 'thumb') }}" alt="" class="inline h-12 w-12 object-cover rounded" /></p>
    @endif
    @error('image')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
