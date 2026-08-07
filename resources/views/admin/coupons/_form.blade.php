@php
    /** @var \App\Models\Coupon|null $coupon */
    $selectedProductIds = old('product_ids', $coupon?->products?->pluck('id')->all() ?? []);
    $selectedCategoryIds = old('category_ids', $coupon?->categories?->pluck('id')->all() ?? []);
    $selectedUserIds = old('user_ids', $coupon?->users?->pluck('id')->all() ?? []);
    $autoApply = (bool) old('auto_apply', $coupon?->auto_apply ?? false);
    $isSecret = (bool) old('is_secret', $coupon?->is_secret ?? false);
    $discountType = old('discount_type', $coupon?->discount_type ?? 'percentage');
    $productScope = old('product_scope', $coupon?->product_scope ?? 'all');
    $userScope = old('user_scope', $coupon?->user_scope ?? 'all');
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="code" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Coupon code') }} *</label>
        <x-input type="text" name="code" id="code" value="{{ old('code', $coupon?->code) }}" class="block w-full uppercase" required />
        @error('code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="label" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Label') }} *</label>
        <x-input type="text" name="label" id="label" value="{{ old('label', $coupon?->label) }}" class="block w-full" required />
        @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div>
    <label for="description" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Description') }}</label>
    <textarea name="description" id="description" rows="3" class="block w-full rounded-lg border border-gray-300 px-3 py-2">{{ old('description', $coupon?->description) }}</textarea>
    @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="from_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('From date') }} *</label>
        <x-input type="date" name="from_date" id="from_date" value="{{ old('from_date', $coupon?->from_date?->format('Y-m-d')) }}" class="block w-full" required />
        @error('from_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="to_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('To date') }} *</label>
        <x-input type="date" name="to_date" id="to_date" value="{{ old('to_date', $coupon?->to_date?->format('Y-m-d')) }}" class="block w-full" required />
        @error('to_date')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <label for="discount_type" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Discount type') }} *</label>
        <select name="discount_type" id="discount_type" class="block w-full rounded-lg border border-gray-300 px-3 py-2" data-discount-type>
            <option value="percentage" @selected($discountType === 'percentage')>{{ __('Percentage') }}</option>
            <option value="fixed" @selected($discountType === 'fixed')>{{ __('Fixed amount') }}</option>
        </select>
        @error('discount_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="discount_amount" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Discount amount') }} *</label>
        <x-input type="number" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $coupon?->discount_amount) }}" step="0.01" min="0.01" class="block w-full" required />
        <p class="mt-1 text-xs text-gray-500" data-discount-hint>{{ __('Percent or fixed currency amount') }}</p>
        @error('discount_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div data-max-discount-wrap @class(['hidden' => $discountType !== 'percentage'])>
    <label for="max_discount_amount" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Max discount amount') }} *</label>
    <x-input type="number" name="max_discount_amount" id="max_discount_amount" value="{{ old('max_discount_amount', $coupon?->max_discount_amount) }}" step="0.01" min="0.01" class="block w-full" />
    @error('max_discount_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="min_order_amount" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Minimum order amount') }}</label>
    <x-input type="number" name="min_order_amount" id="min_order_amount" value="{{ old('min_order_amount', $coupon?->min_order_amount) }}" step="0.01" min="0" class="block w-full" />
    @error('min_order_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div>
    <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }} *</label>
    <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2">
        <option value="active" @selected(old('status', $coupon?->status ?? 'active') === 'active')>{{ __('Active') }}</option>
        <option value="inactive" @selected(old('status', $coupon?->status) === 'inactive')>{{ __('Inactive') }}</option>
    </select>
    @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>

<div class="flex items-center gap-3">
    <input type="hidden" name="auto_apply" value="0" />
    <input type="checkbox" name="auto_apply" id="auto_apply" value="1" @checked($autoApply) data-auto-apply class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
    <label for="auto_apply" class="text-sm font-medium text-gray-700">{{ __('Auto apply coupon to all orders') }}</label>
</div>
@error('auto_apply')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

<div class="flex items-center gap-3">
    <input type="hidden" name="is_secret" value="0" />
    <x-toggle-switch
        name="is_secret"
        :checked="$isSecret"
        label="{{ __('Secret coupon (hidden from checkout offers list)') }}"
    />
</div>
@error('is_secret')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

<div data-scope-sections @class(['hidden' => $autoApply])>
    <fieldset class="space-y-3">
        <legend class="text-sm font-medium text-gray-700">{{ __('Product scope') }}</legend>
        <label class="flex items-center gap-2"><input type="radio" name="product_scope" value="all" @checked($productScope === 'all') data-product-scope /> {{ __('All products') }}</label>
        <label class="flex items-center gap-2"><input type="radio" name="product_scope" value="products" @checked($productScope === 'products') data-product-scope /> {{ __('Specific products') }}</label>
        <label class="flex items-center gap-2"><input type="radio" name="product_scope" value="categories" @checked($productScope === 'categories') data-product-scope /> {{ __('Specific categories') }}</label>
    </fieldset>

    <div data-product-ids-wrap @class(['mt-4', 'hidden' => $productScope !== 'products'])>
        <label for="product_ids" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Products') }}</label>
        <select name="product_ids[]" id="product_ids" multiple class="block w-full rounded-lg border border-gray-300" data-placeholder="{{ __('Search products…') }}">
            @foreach($products as $product)
                <option value="{{ $product->id }}" @selected(in_array($product->id, $selectedProductIds))>{{ $product->name_en }}</option>
            @endforeach
        </select>
        @error('product_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div data-category-ids-wrap @class(['mt-4', 'hidden' => $productScope !== 'categories'])>
        <label for="category_ids" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Categories') }}</label>
        <select name="category_ids[]" id="category_ids" multiple class="block w-full rounded-lg border border-gray-300" data-placeholder="{{ __('Search categories…') }}">
            @foreach($categories as $category)
                <option value="{{ $category->id }}" @selected(in_array($category->id, $selectedCategoryIds))>{{ $category->name_en }}</option>
            @endforeach
        </select>
        @error('category_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <fieldset class="mt-6 space-y-3">
        <legend class="text-sm font-medium text-gray-700">{{ __('User scope') }}</legend>
        <label class="flex items-center gap-2"><input type="radio" name="user_scope" value="all" @checked($userScope === 'all') data-user-scope /> {{ __('All users') }}</label>
        <label class="flex items-center gap-2"><input type="radio" name="user_scope" value="users" @checked($userScope === 'users') data-user-scope /> {{ __('Specific customers') }}</label>
    </fieldset>

    <div data-user-ids-wrap @class(['mt-4', 'hidden' => $userScope !== 'users'])>
        <label for="user_ids" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Customers') }}</label>
        <select name="user_ids[]" id="user_ids" multiple class="block w-full rounded-lg border border-gray-300" data-placeholder="{{ __('Search customers…') }}">
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(in_array($customer->id, $selectedUserIds))>{{ $customer->name }} @if($customer->email)({{ $customer->email }})@elseif($customer->phone)({{ $customer->phone }})@endif</option>
            @endforeach
        </select>
        @error('user_ids')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

@push('styles')
<style>
    [data-coupon-form] .select2-container--default .select2-selection--multiple {
        min-height: 42px;
        border-radius: 0.5rem;
        border-color: rgb(209 213 219);
        padding: 0.25rem 0.5rem;
    }
    [data-coupon-form] .select2-container--default.select2-container--focus .select2-selection--multiple,
    [data-coupon-form] .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: rgb(107 114 128);
        outline: 2px solid rgb(107 114 128);
        outline-offset: 2px;
    }
    [data-coupon-form] .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border-radius: 0.375rem;
        background-color: rgb(238 242 255);
        border-color: rgb(199 210 254);
        color: rgb(55 48 163);
        padding: 0.125rem 0.5rem;
    }
    [data-coupon-form] .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgb(79 70 229);
        margin-right: 0.25rem;
    }
    [data-coupon-form] .select2-dropdown {
        border-radius: 0.5rem;
        border-color: rgb(209 213 219);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
</style>
@endpush
