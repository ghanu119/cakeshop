@extends('layouts.admin')

@section('title', __('Sliders'))

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Sliders') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Manage slider groups and their slides. Turn a slider off to hide it on the storefront without deleting its items.') }}</p>
    </header>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Name') }}</x-table.th>
                <x-table.th>{{ __('Slug') }}</x-table.th>
                <x-table.th>{{ __('Items') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($sliders as $slider)
                    <x-table.row>
                        <x-table.cell>
                            <span class="font-medium text-gray-900">{{ $slider->name }}</span>
                            @if($slider->description)
                                <p class="mt-0.5 text-sm text-gray-500">{{ $slider->description }}</p>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="font-mono text-sm text-gray-600">{{ $slider->slug }}</x-table.cell>
                        <x-table.cell>{{ $slider->items_count }}</x-table.cell>
                        <x-table.cell>
                            @can('update', $slider)
                                <form method="post" action="{{ route('admin.sliders.update', $slider) }}" class="inline-flex items-center">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="0" />
                                    <x-toggle-switch
                                        name="is_active"
                                        :checked="$slider->is_active"
                                        :label="$slider->is_active ? __('Active') : __('Inactive')"
                                        submit-on-change
                                    />
                                </form>
                            @else
                                <x-badge :variant="$slider->is_active ? 'success' : 'default'">{{ $slider->is_active ? __('Active') : __('Inactive') }}</x-badge>
                            @endcan
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('slider_items.view')
                                <a href="{{ route('admin.sliders.items.index', $slider) }}" class="text-indigo-600 hover:text-indigo-800">{{ __('Manage items') }}</a>
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="py-16 text-center text-sm text-gray-500">
                            {{ __('No sliders configured yet.') }}
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>
@endsection
