@php
    $selectClass = $selectClass ?? 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-900 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20';
    $selectId = $selectId ?? 'sort';
    $currentSort = request('sort', 'name_asc');
@endphp
<select name="sort" id="{{ $selectId }}" class="{{ $selectClass }}" @if(!empty($autoSubmit)) onchange="this.form.submit()" @endif>
    <option value="name_asc" @selected($currentSort === 'name_asc')>{{ __('Name A–Z') }}</option>
    <option value="name_desc" @selected($currentSort === 'name_desc')>{{ __('Name Z–A') }}</option>
    <option value="price_asc" @selected($currentSort === 'price_asc')>{{ __('Price: Low to High') }}</option>
    <option value="price_desc" @selected($currentSort === 'price_desc')>{{ __('Price: High to Low') }}</option>
    <option value="newest" @selected($currentSort === 'newest')>{{ __('Newest First') }}</option>
</select>
