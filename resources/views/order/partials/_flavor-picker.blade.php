@if(($hasFlavors ?? false) && $product->flavors->isNotEmpty())
    @php
        $initialFlavorId = old('flavor_id', $product->flavors->first()?->id);
    @endphp
    <div class="{{ $wrapperClass ?? 'md:col-span-2' }}">
        <label class="{{ $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700' }}">{{ __('Flavor') }} <span class="text-red-500">*</span></label>
        <input type="hidden" name="flavor_id" id="flavor_id" value="{{ $initialFlavorId }}" />
        <div
            class="{{ $pickerClass ?? 'flex flex-wrap gap-2' }} flavor-picker"
            data-flavor-picker
            role="radiogroup"
            aria-label="{{ __('Flavor') }}"
            @if(!empty($flavorLabelTarget)) data-flavor-label-target="{{ $flavorLabelTarget }}" @endif
        >
            @foreach($product->flavors as $flavor)
                @php $isSelected = (string) $initialFlavorId === (string) $flavor->id; @endphp
                <button
                    type="button"
                    data-flavor-id="{{ $flavor->id }}"
                    data-flavor-label="{{ $flavor->name_en }}"
                    class="{{ $buttonClass ?? 'rounded-full border border-rose-200 bg-white px-3 py-1 text-sm font-medium text-rose-900 hover:bg-rose-50' }}"
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                >{{ $flavor->name_en }}</button>
            @endforeach
        </div>
        @error('flavor_id')<p class="{{ $errorClass ?? 'mt-1 text-sm text-red-600' }}">{{ $message }}</p>@enderror
    </div>
@endif

@include('order.partials._picker-styles')
