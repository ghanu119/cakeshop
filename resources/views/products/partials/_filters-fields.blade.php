@php
    $inputClass = $inputClass ?? 'w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder:text-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $selectClass = $selectClass ?? $inputClass;
    $multiSelectClass = $multiSelectClass ?? $selectClass;
    $hasMultiFilters = (isset($filterFlavors) && $filterFlavors->isNotEmpty())
        || (isset($filterWeights) && $filterWeights->isNotEmpty());
    $priceInputClass = $priceInputClass ?? 'w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $labelClass = $labelClass ?? 'mb-2 block text-sm font-medium text-gray-700';
    $gridClass = $gridClass ?? 'grid gap-4 sm:grid-cols-2 lg:grid-cols-3';
    $selectedFlavorIds = array_map('intval', (array) request('flavor_ids', []));
    $selectedWeightIds = array_map('intval', (array) request('weight_ids', []));
@endphp
<div class="{{ $gridClass }}">
    <div>
        <label for="search" class="{{ $labelClass }}">{{ __('Search') }}</label>
        <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search by name, ingredients, or flavor...') }}" class="{{ $inputClass }}">
    </div>
    <div>
        <label for="category_id" class="{{ $labelClass }}">{{ __('Category') }}</label>
        <select name="category_id" id="category_id" class="{{ $selectClass }}">
            <option value="">{{ __('All Categories') }}</option>
            @foreach($categories as $cat)
                <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name_en }}</option>
            @endforeach
        </select>
    </div>
    @if(isset($filterFlavors) && $filterFlavors->isNotEmpty())
    <div>
        <label for="flavor_ids" class="{{ $labelClass }}">{{ __('Flavor') }}</label>
        <select name="flavor_ids[]" id="flavor_ids" multiple class="{{ $multiSelectClass }} js-product-filter-multi" data-placeholder="{{ __('Select flavors…') }}">
            @foreach($filterFlavors as $flavor)
                <option value="{{ $flavor->id }}" @selected(in_array($flavor->id, $selectedFlavorIds, true))>{{ $flavor->name_en }}</option>
            @endforeach
        </select>
    </div>
    @endif
    @if(isset($filterWeights) && $filterWeights->isNotEmpty())
    <div>
        <label for="weight_ids" class="{{ $labelClass }}">{{ __('Weight') }}</label>
        <select name="weight_ids[]" id="weight_ids" multiple class="{{ $multiSelectClass }} js-product-filter-multi" data-placeholder="{{ __('Select weights…') }}">
            @foreach($filterWeights as $weight)
                <option value="{{ $weight->id }}" @selected(in_array($weight->id, $selectedWeightIds, true))>{{ $weight->label }}</option>
            @endforeach
        </select>
    </div>
    @endif
    @if($priceRange && $priceRange->min_price !== null)
    <div>
        <label class="{{ $labelClass }}">{{ __('Price range') }}</label>
        <div class="flex items-center gap-2">
            <input type="number" name="price_min" id="price_min" value="{{ request('price_min') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Min') }}" class="{{ $priceInputClass }}">
            <span class="text-gray-400">–</span>
            <input type="number" name="price_max" id="price_max" value="{{ request('price_max') }}" min="{{ $priceRange->min_price }}" max="{{ $priceRange->max_price }}" step="1" placeholder="{{ __('Max') }}" class="{{ $priceInputClass }}">
        </div>
    </div>
    @endif
    <div>
        <label for="sort" class="{{ $labelClass }}">{{ __('Sort By') }}</label>
        <select name="sort" id="sort" class="{{ $selectClass }}">
            <option value="name_asc" @selected(request('sort', 'name_asc') === 'name_asc')>{{ __('Name A–Z') }}</option>
            <option value="name_desc" @selected(request('sort') === 'name_desc')>{{ __('Name Z–A') }}</option>
            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ __('Price: Low to High') }}</option>
            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ __('Price: High to Low') }}</option>
            <option value="newest" @selected(request('sort') === 'newest')>{{ __('Newest First') }}</option>
        </select>
    </div>
</div>

@if($hasMultiFilters)
@push('styles')
<style>
    #product-filters .select2-container--default .select2-selection--multiple {
        min-height: 2.75rem;
        border-radius: 0.75rem;
        border-color: rgb(253 230 138 / 0.6);
        background-color: rgb(255 251 235 / 0.3);
        padding: 0.25rem 0.5rem;
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
    }
    #product-filters .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: rgb(180 83 9);
        margin-right: 0.25rem;
    }
    #product-filters .select2-dropdown {
        border-radius: 0.75rem;
        border-color: rgb(253 230 138 / 0.6);
    }
</style>
@endpush
@push('scripts')
    @vite('resources/js/product-filters-select2.js')
@endpush
@endif
