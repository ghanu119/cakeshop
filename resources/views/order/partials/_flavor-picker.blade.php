@if(($hasFlavors ?? false) && $product->flavors->isNotEmpty())
    @php
        $initialFlavorId = old('flavor_id');
        $hasFlavorError = $errors->has('flavor_id');
    @endphp
    <div class="{{ $wrapperClass ?? 'md:col-span-2' }}">
        <label class="{{ $labelClass ?? 'mb-1 block text-sm font-medium text-gray-700' }}">{{ __('Flavor') }} <span class="text-red-500">*</span></label>
        <input type="hidden" name="flavor_id" id="flavor_id" value="{{ $initialFlavorId }}" />
        <div
            class="{{ $pickerClass ?? 'flex flex-wrap gap-2' }} flavor-picker @if($hasFlavorError) flavor-picker--error @endif"
            data-flavor-picker
            data-flavor-required
            role="radiogroup"
            aria-label="{{ __('Flavor') }}"
            @if(!empty($flavorLabelTarget)) data-flavor-label-target="{{ $flavorLabelTarget }}" @endif
        >
            @foreach($product->flavors as $flavor)
                @php $isSelected = filled($initialFlavorId) && (string) $initialFlavorId === (string) $flavor->id; @endphp
                <button
                    type="button"
                    data-flavor-id="{{ $flavor->id }}"
                    data-flavor-label="{{ $flavor->name_en }}"
                    class="{{ $buttonClass ?? 'rounded-full border border-rose-200 bg-white px-3 py-1 text-sm font-medium text-rose-900 hover:bg-rose-50' }}"
                    aria-pressed="{{ $isSelected ? 'true' : 'false' }}"
                >{{ $flavor->name_en }}</button>
            @endforeach
        </div>
        <p
            data-flavor-error
            class="{{ $errorClass ?? 'mt-1 text-sm text-red-600' }} @if(!$hasFlavorError) hidden @endif"
            @if($hasFlavorError) role="alert" @endif
        >@if($hasFlavorError){{ $errors->first('flavor_id') }}@endif</p>
    </div>
@endif

@include('order.partials._picker-styles')
