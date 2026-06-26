@props(['slider', 'sliderItem'])

@php
    use App\Models\SliderItem;
    $currentType = old('type', $sliderItem?->type ?? SliderItem::TYPE_IMAGE);
@endphp

<div class="space-y-2" id="slider-item-type-fields" data-initial-type="{{ $currentType }}">
    <label class="block text-sm font-medium text-gray-700">{{ __('Slide type') }}</label>
    <div class="flex flex-wrap gap-4">
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="type" value="{{ SliderItem::TYPE_IMAGE }}" @checked($currentType === SliderItem::TYPE_IMAGE) class="border-gray-300 text-indigo-600 focus:ring-indigo-500" data-role="type-radio" />
            <span class="text-sm text-gray-700">{{ __('Image') }}</span>
        </label>
        <label class="inline-flex items-center gap-2">
            <input type="radio" name="type" value="{{ SliderItem::TYPE_VIDEO }}" @checked($currentType === SliderItem::TYPE_VIDEO) class="border-gray-300 text-indigo-600 focus:ring-indigo-500" data-role="type-radio" />
            <span class="text-sm text-gray-700">{{ __('Video (YouTube, Vimeo, or URL)') }}</span>
        </label>
    </div>
    @error('type')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div data-role="image-fields" class="@if($currentType !== SliderItem::TYPE_IMAGE) hidden @endif">
    @include('admin.sliders.items.partials._slide-image', ['sliderItem' => $sliderItem])
</div>

<div data-role="video-fields" class="@if($currentType !== SliderItem::TYPE_VIDEO) hidden @endif space-y-1">
    <label for="video_url" class="block text-sm font-medium text-gray-700">{{ __('Video URL') }} <span class="text-red-600">*</span></label>
    <x-input type="url" name="video_url" id="video_url" value="{{ old('video_url', $sliderItem?->video_url) }}" class="block w-full" placeholder="https://www.youtube.com/watch?v=…" />
    <p class="text-xs text-gray-500">{{ __('YouTube, Vimeo, direct MP4/WebM, or any embeddable URL.') }}</p>
    @error('video_url')
        <p class="text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="title" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Title') }}</label>
    <x-input type="text" name="title" id="title" value="{{ old('title', $sliderItem?->title) }}" class="block w-full" placeholder="{{ __('Optional overlay text on the left') }}" />
    @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="link" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Link') }}</label>
    <x-input type="text" name="link" id="link" value="{{ old('link', $sliderItem?->link) }}" class="block w-full" placeholder="{{ __('https://… or /products') }}" />
    <p class="mt-1 text-xs text-gray-500">{{ __('Optional. When set, clicking the slide opens this URL.') }}</p>
    @error('link')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label for="sort_order" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Sort order') }}</label>
    <x-input type="number" name="sort_order" id="sort_order" value="{{ old('sort_order', $sliderItem?->sort_order ?? 0) }}" min="0" class="block w-full" />
    @error('sort_order')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div>
    <label class="inline-flex items-center gap-2">
        <input type="hidden" name="is_active" value="0" />
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $sliderItem?->is_active ?? true)) class="rounded border-gray-300 focus:ring-gray-500" />
        <span class="text-sm font-medium text-gray-700">{{ __('Active') }}</span>
    </label>
    @error('is_active')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>
