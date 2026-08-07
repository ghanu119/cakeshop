@props(['weight'])

<div class="space-y-5">
    <div>
        <label for="label" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Display label') }} <span class="text-red-500">*</span></label>
        <input
            type="text"
            name="label"
            id="label"
            value="{{ old('label', $weight?->label) }}"
            required
            autocomplete="off"
            placeholder="{{ __('e.g. 500 gm, 1 KG') }}"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('Shown to customers on the product and checkout pages.') }}</p>
        @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="person_capacity_label" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Person capacity label') }}</label>
        <input
            type="text"
            name="person_capacity_label"
            id="person_capacity_label"
            value="{{ old('person_capacity_label', $weight?->person_capacity_label) }}"
            autocomplete="off"
            placeholder="{{ __('e.g. 4 - 5 People') }}"
            class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('Optional. Shown below the weight picker on product pages (e.g. 8 - 10 People).') }}</p>
        @error('person_capacity_label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="grams" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Weight (grams)') }} <span class="text-red-500">*</span></label>
        <input
            type="number"
            name="grams"
            id="grams"
            value="{{ old('grams', $weight?->grams) }}"
            required
            min="1"
            step="1"
            autocomplete="off"
            placeholder="500"
            class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('Used for sorting and reports (e.g. 250, 500, 1000 for 1 KG).') }}</p>
        @error('grams')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="delivery_charge" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Delivery charge') }}</label>
        <input
            type="number"
            name="delivery_charge"
            id="delivery_charge"
            value="{{ old('delivery_charge', $weight?->delivery_charge) }}"
            min="0"
            step="0.01"
            autocomplete="off"
            placeholder="0.00"
            class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
        />
        <p class="mt-1.5 text-sm text-gray-500">{{ __('Charged on delivery orders for products using this weight. Enter 0 for free delivery, or leave empty to use the site-wide default delivery charge from Settings.') }}</p>
        @error('delivery_charge')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="sort_order" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
            <input
                type="number"
                name="sort_order"
                id="sort_order"
                value="{{ old('sort_order', $weight?->sort_order ?? 0) }}"
                min="0"
                step="1"
                autocomplete="off"
                class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            />
            <p class="mt-1.5 text-sm text-gray-500">{{ __('Lower numbers appear first.') }}</p>
            @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
            <select
                name="status"
                id="status"
                class="block w-full max-w-xs rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-base text-gray-900 shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/20"
            >
                <option value="active" @selected(old('status', $weight?->status ?? 'active') === 'active')>{{ __('Active') }}</option>
                <option value="inactive" @selected(old('status', $weight?->status) === 'inactive')>{{ __('Inactive') }}</option>
            </select>
            <p class="mt-1.5 text-sm text-gray-500">{{ __('Inactive weights are hidden when adding variants to products.') }}</p>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</div>
