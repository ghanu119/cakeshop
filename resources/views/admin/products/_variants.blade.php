@php
    $existingVariants = [];
    if ($product) {
        foreach ($product->variants as $variant) {
            $weightSel = $variant->selections->first(fn ($s) => $s->value && $s->type?->slug === 'weight');
            if ($weightSel) {
                $existingVariants[] = [
                    'id' => $variant->id,
                    'variant_option_value_id' => $weightSel->variant_option_value_id,
                    'label' => $weightSel->value->label,
                    'price' => $variant->price,
                ];
            }
        }
    }

    $variantRows = old('variants', $existingVariants);
    if (! is_array($variantRows)) {
        $variantRows = [];
    }

    $weightLabelsById = $weightValues->keyBy('id')->map(fn ($v) => $v->label);
    $weightOptions = $weightValues->map(fn ($v) => ['id' => $v->id, 'label' => $v->label])->values();
    $nextIndex = count($variantRows);
@endphp

<div class="mt-6 rounded-lg border border-gray-200 p-4" id="product-variants-section">
    <h3 class="mb-2 text-lg font-semibold text-gray-900">{{ __('Prices by weight') }}</h3>
    <p class="mb-4 text-sm text-gray-500">{{ __('Add each weight and its price. Leave empty to use one price for the whole product. Manage weight options under Settings → Cake weights.') }}</p>

    <div class="mb-4 flex flex-wrap items-end gap-3">
        <div class="min-w-[200px] flex-1">
            <label for="variant-weight-add" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Weight') }}</label>
            <select id="variant-weight-add" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900">
                <option value="">{{ __('Select weight…') }}</option>
            </select>
        </div>
        <button type="button" id="variant-add-btn" class="rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white hover:bg-gray-700">{{ __('Add row') }}</button>
    </div>

    <div id="variant-rows" class="space-y-3">
        @foreach($variantRows as $index => $row)
            @php
                $valueId = (int) ($row['variant_option_value_id'] ?? 0);
                $label = $row['label'] ?? $weightLabelsById->get($valueId) ?? __('Weight');
            @endphp
            @include('admin.products._variant-row', [
                'index' => $index,
                'row' => $row,
                'weightLabel' => $label,
            ])
        @endforeach
    </div>

    @error('variants')
        <p class="mt-2 text-sm font-medium text-red-600">{{ $message }}</p>
    @enderror
</div>

<template id="variant-row-template">
    <div class="variant-row flex flex-wrap items-start gap-3 rounded-lg border border-gray-200 bg-gray-50 p-3" data-value-id="__VALUE_ID__">
        <input type="hidden" name="variants[__INDEX__][variant_option_value_id]" value="__VALUE_ID__" />
        <span class="min-w-[120px] pt-2 font-medium text-gray-800">__LABEL__</span>
        <div class="flex flex-col">
            <label class="mb-1 text-sm font-medium text-gray-700" for="variants-__INDEX__-price">{{ __('Price (INR)') }}</label>
            <input
                type="text"
                inputmode="decimal"
                name="variants[__INDEX__][price]"
                id="variants-__INDEX__-price"
                value=""
                autocomplete="off"
                class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
            />
        </div>
        <button type="button" class="variant-remove mt-7 text-sm font-medium text-red-600 hover:text-red-800">{{ __('Remove') }}</button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allWeights = @json($weightOptions);
    let rowIndex = {{ (int) $nextIndex }};
    const rowsEl = document.getElementById('variant-rows');
    const addSelect = document.getElementById('variant-weight-add');
    const addBtn = document.getElementById('variant-add-btn');
    const priceHint = document.getElementById('price-hint-starting');
    const customWeightWrap = document.getElementById('custom-weight-wrap');
    const template = document.getElementById('variant-row-template');

    function usedValueIds() {
        return Array.from(rowsEl.querySelectorAll('.variant-row')).map(el => parseInt(el.dataset.valueId, 10));
    }

    function refreshAddDropdown() {
        const used = usedValueIds();
        addSelect.innerHTML = '<option value="">{{ __("Select weight…") }}</option>';
        allWeights.forEach(w => {
            if (!used.includes(w.id)) {
                const opt = document.createElement('option');
                opt.value = w.id;
                opt.textContent = w.label;
                addSelect.appendChild(opt);
            }
        });
    }

    function updatePriceHint() {
        const hasVariants = rowsEl.querySelectorAll('.variant-row').length > 0;

        if (hasVariants) {
            priceHint?.classList.remove('hidden');
        } else {
            priceHint?.classList.add('hidden');
        }

        // A product can't have both a per-weight price ladder and its own flat
        // delivery charge — hide (and clear) that field once variants exist.
        customWeightWrap?.classList.toggle('hidden', hasVariants);
        if (hasVariants) {
            const deliveryChargeInput = document.getElementById('delivery_charge');
            if (deliveryChargeInput) {
                deliveryChargeInput.value = '';
            }
        }
    }

    function bindRemove(row) {
        row.querySelector('.variant-remove')?.addEventListener('click', () => {
            row.remove();
            refreshAddDropdown();
            updatePriceHint();
        });
    }

    function addRow(valueId, label) {
        const html = template.innerHTML
            .replace(/__INDEX__/g, String(rowIndex))
            .replace(/__VALUE_ID__/g, String(valueId))
            .replace(/__LABEL__/g, label);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        rowsEl.appendChild(row);
        bindRemove(row);
        rowIndex++;
        refreshAddDropdown();
        updatePriceHint();
    }

    rowsEl.querySelectorAll('.variant-row').forEach(bindRemove);

    addBtn.addEventListener('click', () => {
        const id = parseInt(addSelect.value, 10);
        if (!id) return;
        const w = allWeights.find(x => x.id === id);
        addRow(id, w?.label || '');
        addSelect.value = '';
    });

    refreshAddDropdown();
    updatePriceHint();
});
</script>
