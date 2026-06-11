@extends('layouts.admin')

@section('title', __('Features'))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Features') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage home page “Why Choose Us” features') }}</p>
        </div>
        @can('features.create')
            <a href="{{ route('admin.features.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-indigo-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add Feature') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.features.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Title or description…') }}" class="block w-full" />
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
            @if(request()->hasAny(['search', 'is_active']))
                <a href="{{ route('admin.features.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Title') }}</x-table.th>
                <x-table.th>{{ __('Icon') }}</x-table.th>
                <x-table.th>{{ __('Sort order') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($features as $feature)
                    <x-table.row>
                        <x-table.cell>
                            <span class="font-medium text-gray-900">{{ $feature->title }}</span>
                            @if($feature->description)
                                <p class="mt-0.5 text-sm text-gray-500 line-clamp-2">{{ Str::limit($feature->description, 60) }}</p>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-gray-600">{{ $feature->icon ?: '—' }}</x-table.cell>
                        <x-table.cell>{{ $feature->sort_order }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$feature->is_active ? 'success' : 'default'">{{ $feature->is_active ? __('Active') : __('Inactive') }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('features.update')
                                <a href="{{ route('admin.features.edit', $feature) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('features.delete')
                                <x-admin-delete-form
                                    :action="route('admin.features.destroy', $feature)"
                                    :title="__('Delete this feature?')"
                                />
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="py-16 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                <div class="flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                                    <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ __('No features yet') }}</p>
                                <p class="text-center text-sm text-gray-500">{{ __('Add features to show on the home page “Why Choose Us” section.') }}</p>
                                @can('features.create')
                                    <a href="{{ route('admin.features.create') }}" class="mt-1 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition duration-200 hover:bg-indigo-700">+ {{ __('Add Feature') }}</a>
                                @endcan
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($features->hasPages())
        <div class="mt-4">
            {{ $features->links() }}
        </div>
    @endif
@endsection
