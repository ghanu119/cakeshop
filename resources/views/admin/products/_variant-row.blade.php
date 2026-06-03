@props(['index', 'row', 'weightLabel'])

<div class="variant-row flex flex-wrap items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3" data-value-id="{{ $row['variant_option_value_id'] }}">
    <input type="hidden" name="variants[{{ $index }}][variant_option_value_id]" value="{{ $row['variant_option_value_id'] }}" />
    @if(!empty($row['id']))
        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $row['id'] }}" />
    @endif
    <span class="min-w-[120px] pt-2 font-medium text-gray-800">{{ $weightLabel }}</span>
    <div class="flex flex-col">
        <label class="mb-1 text-sm font-medium text-gray-700" for="variants-{{ $index }}-price">{{ __('Price (INR)') }}</label>
        <input
            type="text"
            inputmode="decimal"
            name="variants[{{ $index }}][price]"
            id="variants-{{ $index }}-price"
            value="{{ old('variants.'.$index.'.price', $row['price'] ?? '') }}"
            autocomplete="off"
            class="w-36 rounded-lg border px-3 py-2 text-base text-gray-900 shadow-sm {{ $errors->has('variants.'.$index.'.price') ? 'border-red-500 focus:border-red-500 focus:ring-red-500/20' : 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500/20' }}"
        />
        @error('variants.'.$index.'.price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('variants.'.$index.'.variant_option_value_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
        @error('variants.'.$index.'.id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <button type="button" class="variant-remove mt-7 text-sm font-medium text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
</div>
