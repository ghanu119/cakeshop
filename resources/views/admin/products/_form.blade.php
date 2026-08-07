@php
    /** @var \App\Models\Product|null $product */
    /** @var \Illuminate\Support\Collection $categories */
    /** @var \Illuminate\Support\Collection $weightValues */
    /** @var \Illuminate\Support\Collection $flavors */
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
    <label for="sku" class="mb-1 block text-sm font-medium text-gray-700">{{ __('SKU') }} <span class="font-normal text-gray-500">({{ __('Optional') }})</span></label>
    <x-input type="text" name="sku" id="sku" value="{{ old('sku', $product?->sku) }}" class="block w-full" placeholder="CAKE-CHOC-001" />
    @error('sku')
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
    <label for="message_on_cake_max_length" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Message on cake — max characters') }} <span class="font-normal text-gray-500">({{ __('Optional') }})</span></label>
    <x-input type="number" name="message_on_cake_max_length" id="message_on_cake_max_length" value="{{ old('message_on_cake_max_length', $product?->message_on_cake_max_length) }}" min="{{ \App\Models\Order::MESSAGE_ON_CAKE_MIN_LENGTH }}" max="{{ \App\Models\Order::MESSAGE_ON_CAKE_LIMIT_MAX }}" class="block w-full" placeholder="{{ \App\Models\Order::defaultMessageOnCakeMaxLength() }}" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty to use the site default (:default characters). Use a lower limit for small cakes or a higher limit for large sheet cakes.', ['default' => \App\Models\Order::defaultMessageOnCakeMaxLength()]) }}</p>
    @error('message_on_cake_max_length')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="earliest_delivery_label" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Earliest delivery label') }} <span class="font-normal text-gray-500">({{ __('Optional') }})</span></label>
    <x-input type="text" name="earliest_delivery_label" id="earliest_delivery_label" value="{{ old('earliest_delivery_label', $product?->earliest_delivery_label) }}" class="block w-full" placeholder="{{ __('e.g. 1–2 hours, 4–5 hours, 1–2 days') }}" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Shown on the product page and checkout to set customer expectations.') }}</p>
    @error('earliest_delivery_label')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="min_hours_before_delivery" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Minimum hours before delivery') }} <span class="font-normal text-gray-500">({{ __('Optional') }})</span></label>
    <x-input type="number" name="min_hours_before_delivery" id="min_hours_before_delivery" value="{{ old('min_hours_before_delivery', $product?->min_hours_before_delivery) }}" min="1" max="720" class="block w-full" placeholder="{{ settings('order_min_hours_before_delivery') ?? 4 }}" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Leave empty to use the site default (:default hours). Overrides the earliest selectable delivery time for this product.', ['default' => settings('order_min_hours_before_delivery') ?? 4]) }}</p>
    @error('min_hours_before_delivery')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="inline-flex items-center gap-2">
        <input type="checkbox" name="show_whatsapp_customize_help" value="1" @checked(old('show_whatsapp_customize_help', $product?->show_whatsapp_customize_help)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Show “Need help customizing? Connect now” on product page') }}</span>
    </label>
    <p class="mt-1 text-xs text-gray-500">{{ __('Uses the site contact number from Settings for WhatsApp.') }}</p>
    @error('show_whatsapp_customize_help')
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

<div id="custom-weight-wrap">
    <label for="delivery_charge" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Delivery charge') }}</label>
    <x-input type="number" name="delivery_charge" id="delivery_charge" value="{{ old('delivery_charge', $product?->delivery_charge) }}" step="0.01" min="0" class="block w-full max-w-xs" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Charged on delivery orders for this product. Leave empty to use the site-wide default. Only applies when there are no per-weight prices below (those use their own weight\'s delivery charge instead).') }}</p>
    @error('delivery_charge')
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

@include('admin.products.partials._images-manager', ['product' => $product])
