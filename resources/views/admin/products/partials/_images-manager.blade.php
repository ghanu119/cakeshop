@php
    use App\Services\ProductImageTempService;

    $product = $product ?? null;
    $maxImages = \App\Services\ProductImageService::MAX_IMAGES;
    $maxImageBytes = \App\Services\ProductImageTempService::MAX_BYTES;
    $maxImageSizeLabel = number_format($maxImageBytes / 1024 / 1024, 0).' MB';
    $existingImages = $product
        ? $product->orderedProductImages()->map(fn ($media) => [
            'ref' => 'existing:'.$media->id,
            'id' => $media->id,
            'url' => ProductImageTempService::normalizePreviewUrl($product->productImageUrl($media, 'thumb')),
            'fullUrl' => ProductImageTempService::normalizePreviewUrl($product->productImageUrl($media, 'large')),
            'name' => $media->name,
        ])->values()->all()
        : [];
@endphp

<div id="product-images-manager"
     class="space-y-4"
     data-max-images="{{ $maxImages }}"
     data-max-bytes="{{ $maxImageBytes }}"
     data-size-exceeded-message="{{ __('Image size exceeds the maximum of :max.', ['max' => $maxImageSizeLabel]) }}"
     data-upload-url="{{ route('admin.products.images.temp.store') }}"
     data-delete-url-template="{{ route('admin.products.images.temp.destroy', ['token' => '__TOKEN__']) }}"
     data-existing='@json($existingImages)'>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <label class="block text-sm font-medium text-gray-700">{{ __('Product images') }}</label>
        <p class="text-xs text-gray-500">{{ __('Up to :max images, :size each. First image is primary.', ['max' => $maxImages, 'size' => $maxImageSizeLabel]) }}</p>
    </div>

    <div data-role="preview-grid"></div>

    <div class="flex flex-wrap items-center gap-3">
        <button type="button" data-role="pick-files" class="inline-flex cursor-pointer items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
            {{ __('Add images') }}
        </button>
        <input type="file" data-role="file-input" accept="image/*" multiple class="hidden" />
        <p data-role="status" class="text-sm text-gray-500"></p>
    </div>

    <div data-role="hidden-fields" class="hidden"></div>

    @error('product_images')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('product_images.*')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('primary_image')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('removed_media_ids')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
    @error('removed_media_ids.*')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
