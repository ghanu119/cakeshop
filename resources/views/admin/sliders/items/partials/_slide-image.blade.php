@php
    use App\Services\ProductImageTempService;
    use App\Services\SliderItemImageTempService;

    $sliderItem = $sliderItem ?? null;
    $maxImageBytes = ProductImageTempService::MAX_BYTES;
    $maxImageSizeLabel = number_format($maxImageBytes / 1024 / 1024, 0).' MB';
    $media = $sliderItem?->slideImageMedia();
    $existingImage = $media
        ? [
            'ref' => 'existing:'.$media->id,
            'id' => $media->id,
            'url' => SliderItemImageTempService::normalizePreviewUrl($sliderItem->imageUrl('thumb')),
            'fullUrl' => SliderItemImageTempService::normalizePreviewUrl($sliderItem->imageUrl('large')),
            'name' => $media->name,
        ]
        : null;
@endphp

<div id="slider-item-image-manager"
     class="space-y-4"
     data-max-bytes="{{ $maxImageBytes }}"
     data-size-exceeded-message="{{ __('Image size exceeds the maximum of :max.', ['max' => $maxImageSizeLabel]) }}"
     data-upload-url="{{ route('admin.sliders.items.images.temp.store') }}"
     data-delete-url-template="{{ route('admin.sliders.items.images.temp.destroy', ['token' => '__TOKEN__']) }}"
     data-existing='@json($existingImage)'>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="block text-sm font-medium text-gray-700">{{ __('Slide image') }} <span class="text-red-600">*</span></label>
        <p class="text-xs text-gray-500">{{ __('Recommended :dimensions (1690×790). Up to :size. JPG, PNG, GIF, or WebP.', ['dimensions' => __('landscape image'), 'size' => $maxImageSizeLabel]) }}</p>
    </div>

    <div data-role="preview-area" class="hidden">
        <div class="product-image-thumb relative inline-block h-40 w-64 max-w-full">
            <div data-role="preview-frame" class="h-full w-full overflow-hidden rounded-lg border border-gray-200 bg-gray-100"></div>
            <div class="absolute inset-x-0 bottom-0 flex gap-1 bg-black/60 p-1">
                <button type="button" data-role="remove-image" class="rounded bg-red-600 px-2 py-0.5 text-[10px] font-semibold text-white">
                    {{ __('Remove') }}
                </button>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-role="pick-file" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            {{ __('Choose image') }}
        </button>
        <input type="file" data-role="file-input" accept="image/*" class="hidden" />
        <p data-role="status" class="text-sm text-gray-500"></p>
    </div>

    <input type="hidden" name="slide_image_ref" data-role="slide-image-ref" value="{{ old('slide_image_ref') }}" />
    <input type="hidden" name="remove_slide_image" data-role="remove-slide-image" value="{{ old('remove_slide_image', '0') }}" />

    @error('slide_image_ref')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
