@extends('layouts.admin')

@section('title', __('Flavors'))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Flavors') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage cake flavors for products and orders') }}</p>
        </div>
        @can('flavors.create')
            <a href="{{ route('admin.flavors.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-indigo-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add Flavor') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.flavors.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Search by name') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search…') }}" class="block w-full" />
            </div>
            <div class="w-44">
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-gray-900 transition duration-200 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30">
                    <option value="">{{ __('All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <x-button type="submit" variant="primary">{{ __('Filter') }}</x-button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.flavors.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Name (EN)') }}</x-table.th>
                <x-table.th>{{ __('Slug') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th>{{ __('Sort order') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($flavors as $flavor)
                    <x-table.row>
                        <x-table.cell>{{ $flavor->name_en }}</x-table.cell>
                        <x-table.cell>{{ $flavor->slug }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$flavor->isActive() ? 'success' : 'default'">{{ $flavor->status }}</x-badge>
                        </x-table.cell>
                        <x-table.cell>{{ $flavor->sort_order }}</x-table.cell>
                        <x-table.cell class="text-right">
                            @can('flavors.update')
                                <a href="{{ route('admin.flavors.edit', $flavor) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('flavors.delete')
                                <form method="post" action="{{ route('admin.flavors.destroy', $flavor) }}" class="inline" onsubmit="return confirm('{{ __('Delete this flavor?') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800">{{ __('Delete') }}</button>
                                </form>
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="py-16 text-center">
                            <div class="mx-auto flex max-w-sm flex-col items-center gap-3">
                                <p class="text-sm font-medium text-gray-900">{{ __('No flavors yet') }}</p>
                                @can('flavors.create')
                                    <a href="{{ route('admin.flavors.create') }}" class="mt-1 inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition duration-200 hover:bg-indigo-700">+ {{ __('Add Flavor') }}</a>
                                @endcan
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($flavors->hasPages())
        <div class="mt-4">
            {{ $flavors->links() }}
        </div>
    @endif
@endsection
