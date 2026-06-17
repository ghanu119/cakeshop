@php
    $inputClass = $inputClass ?? 'w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $selectClass = $selectClass ?? $inputClass;
    $multiSelectClass = $multiSelectClass ?? $selectClass;
    $hasMultiFilters = (isset($filterFlavors) && $filterFlavors->isNotEmpty())
        || (isset($filterWeights) && $filterWeights->isNotEmpty());
    $priceInputClass = $priceInputClass ?? 'w-full min-w-0 rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $labelClass = $labelClass ?? 'mb-2 block text-sm font-medium text-gray-700';
    $fieldsGridClass = $fieldsGridClass ?? $gridClass ?? 'product-filters-grid grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4';
    $showSort = $showSort ?? true;
    $searchPlaceholder = $searchPlaceholder ?? __('Search by name, ingredients, or flavor...');
    $selectedFlavorIds = array_map('intval', (array) request('flavor_ids', []));
    $selectedWeightIds = array_map('intval', (array) request('weight_ids', []));
    $selectedCategoryId = $selectedCategoryId ?? request('category_id');
@endphp
<div class="product-filters-fields">
    <div class="product-filters-field product-filters-search">
        <label for="search" class="{{ $labelClass }}">{{ __('Search') }}</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}" class="{{ $inputClass }}">
    </div>

    <div class="{{ $fieldsGridClass }}">
        <div class="product-filters-field">
            <label for="category_id" class="{{ $labelClass }}">{{ __('Category') }}</label>
            <select name="category_id" id="category_id" class="{{ $selectClass }}">
                <option value="">{{ __('All Categories') }}</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected($selectedCategoryId == $cat->id)>{{ $cat->name_en }}</option>
                @endforeach
            </select>
        </div>
        @if(isset($filterFlavors) && $filterFlavors->isNotEmpty())
        <div class="product-filters-field">
            <label for="flavor_ids" class="{{ $labelClass }}">{{ __('Flavor') }}</label>
            <select name="flavor_ids[]" id="flavor_ids" multiple class="{{ $multiSelectClass }} js-product-filter-multi" data-placeholder="{{ __('Select flavors…') }}">
                @foreach($filterFlavors as $flavor)
                    <option value="{{ $flavor->id }}" @selected(in_array($flavor->id, $selectedFlavorIds, true))>{{ $flavor->name_en }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if(isset($filterWeights) && $filterWeights->isNotEmpty())
        <div class="product-filters-field">
            <label for="weight_ids" class="{{ $labelClass }}">{{ __('Weight') }}</label>
            <select name="weight_ids[]" id="weight_ids" multiple class="{{ $multiSelectClass }} js-product-filter-multi" data-placeholder="{{ __('Select weights…') }}">
                @foreach($filterWeights as $weight)
                    <option value="{{ $weight->id }}" @selected(in_array($weight->id, $selectedWeightIds, true))>{{ $weight->label }}</option>
                @endforeach
            </select>
        </div>
        @endif
        @if($priceRange && $priceRange->min_price !== null)
        <div class="product-filters-field product-filters-field--price sm:col-span-2 xl:col-span-2">
            <label class="{{ $labelClass }}" for="price_min">{{ __('Price range') }}</label>
            <div class="flex items-center gap-2">
                <input type="number" name="price_min" id="price_min" value="{{ request('price_min') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Min') }}" class="{{ $priceInputClass }}" aria-label="{{ __('Minimum price') }}">
                <span class="shrink-0 text-stone-400" aria-hidden="true">–</span>
                <input type="number" name="price_max" id="price_max" value="{{ request('price_max') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Max') }}" class="{{ $priceInputClass }}" aria-label="{{ __('Maximum price') }}">
            </div>
        </div>
        @endif
        @if($showSort)
        <div class="product-filters-field">
            <label for="sort" class="{{ $labelClass }}">{{ __('Sort By') }}</label>
            @include('products.partials._sort-select', ['selectClass' => $selectClass])
        </div>
        @endif
    </div>
</div>

@if($hasMultiFilters)
@push('styles')
<style>
    #product-filters .product-filters-fields .select2-container {
        width: 100% !important;
    }
    #product-filters .select2-container--default .select2-selection--multiple {
        min-height: 3rem;
        border-radius: 0.75rem;
        border: 1px solid rgb(231 229 228);
        background-color: #fff;
        padding: 0.35rem 0.5rem;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.04);
    }
    #product-filters .select2-container--default.select2-container--focus .select2-selection--multiple,
    #product-filters .select2-container--default.select2-container--open .select2-selection--multiple {
        border-color: rgb(245 158 11);
        outline: 2px solid rgb(245 158 11 / 0.2);
        outline-offset: 0;
    }
    #product-filters .select2-container--default .select2-selection--multiple .select2-selection__choice {
        border-radius: 9999px;
        background-color: rgb(254 243 199);
        border-color: rgb(253 230 138);
        color: rgb(68 64 60);
        padding: 0.125rem 0.5rem;
        font-size: 0.8125rem;
    }
    #product-filters .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgb(180 83 9);
        margin-right: 0.25rem;
    }
    #product-filters .select2-dropdown {
        border-radius: 0.75rem;
        border-color: rgb(253 230 138 / 0.6);
        z-index: 40;
    }
</style>
@endpush
@push('scripts')
    @vite('resources/js/product-filters-select2.js')
@endpush
@endif
