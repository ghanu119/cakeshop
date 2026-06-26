@extends('layouts.admin')

@section('title', __('Edit slide item'))

@section('content')
    <header class="mb-8">
        <p class="mb-1 text-sm text-gray-500">
            <a href="{{ route('admin.sliders.index') }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Sliders') }}</a>
            <span class="mx-1">/</span>
            <a href="{{ route('admin.sliders.items.index', $slider) }}" class="text-indigo-600 hover:text-indigo-800">{{ $slider->name }}</a>
        </p>
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Edit slide item') }}</h1>
    </header>

    <x-card>
        <form method="post" action="{{ route('admin.sliders.items.update', [$slider, $sliderItem]) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('admin.sliders.items._form', ['slider' => $slider, 'sliderItem' => $sliderItem])

            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-6">
                <x-button type="submit" variant="primary">{{ __('Save changes') }}</x-button>
                <a href="{{ route('admin.sliders.items.index', $slider) }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection

@push('scripts')
    @vite(['resources/js/admin-slider-item-form.js', 'resources/js/admin-home-slider-image.js', 'resources/js/image-lightbox.js'])
@endpush
