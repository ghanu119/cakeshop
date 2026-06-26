@extends('layouts.admin')

@section('title', __(':slider items', ['slider' => $slider->name]))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="mb-1 text-sm text-gray-500">
                <a href="{{ route('admin.sliders.index') }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Sliders') }}</a>
                <span class="mx-1">/</span>
                <span>{{ $slider->name }}</span>
            </p>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Slide items') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage slides for :name', ['name' => $slider->name]) }}</p>
        </div>
        @can('slider_items.create')
            <a href="{{ route('admin.sliders.items.create', $slider) }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-indigo-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add item') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.sliders.items.index', $slider) }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Title, link, or video URL…') }}" class="block w-full" />
            </div>
            <div class="w-44">
                <label for="type" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Type') }}</label>
                <select name="type" id="type" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 transition duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">{{ __('All') }}</option>
                    <option value="image" @selected(request('type') === 'image')>{{ __('Image') }}</option>
                    <option value="video" @selected(request('type') === 'video')>{{ __('Video') }}</option>
                </select>
            </div>
            <div class="w-44">
                <label for="is_active" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select name="is_active" id="is_active" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 transition duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" @selected(request('is_active') === '1')>{{ __('Active') }}</option>
                    <option value="0" @selected(request('is_active') === '0')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <x-button type="submit" variant="primary">{{ __('Filter') }}</x-button>
            @if(request()->hasAny(['search', 'is_active', 'type']))
                <a href="{{ route('admin.sliders.items.index', $slider) }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Preview') }}</x-table.th>
                <x-table.th>{{ __('Type') }}</x-table.th>
                <x-table.th>{{ __('Title') }}</x-table.th>
                <x-table.th>{{ __('Link') }}</x-table.th>
                <x-table.th>{{ __('Sort order') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($sliderItems as $item)
                    <x-table.row>
                        <x-table.cell>
                            @if($item->isImage() && $item->hasImage())
                                <img src="{{ $item->imageUrl('thumb') }}" alt="" class="h-12 w-20 rounded object-cover" />
                            @elseif($item->isVideo())
                                <span class="inline-flex items-center rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700">{{ __('Video') }}</span>
                            @else
                                <span class="text-sm text-gray-400">—</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="capitalize">{{ $item->type }}</x-table.cell>
                        <x-table.cell>
                            <span class="font-medium text-gray-900">{{ $item->title ?: '—' }}</span>
                        </x-table.cell>
                        <x-table.cell class="max-w-xs truncate text-gray-600">{{ $item->link ?: ($item->video_url ?: '—') }}</x-table.cell>
                        <x-table.cell>{{ $item->sort_order }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$item->is_active ? 'success' : 'default'">{{ $item->is_active ? __('Active') : __('Inactive') }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('slider_items.update')
                                <a href="{{ route('admin.sliders.items.edit', [$slider, $item]) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('slider_items.delete')
                                <x-admin-delete-form
                                    :action="route('admin.sliders.items.destroy', [$slider, $item])"
                                    :title="__('Delete this slide item?')"
                                />
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="7" class="py-16 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                <p class="text-sm font-medium text-gray-900">{{ __('No slide items yet') }}</p>
                                <p class="text-center text-sm text-gray-500">{{ __('Add image or video slides for this slider.') }}</p>
                                @can('slider_items.create')
                                    <a href="{{ route('admin.sliders.items.create', $slider) }}" class="mt-1 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition duration-200 hover:bg-indigo-700">+ {{ __('Add item') }}</a>
                                @endcan
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($sliderItems->hasPages())
        <div class="mt-4">
            {{ $sliderItems->links() }}
        </div>
    @endif
@endsection
