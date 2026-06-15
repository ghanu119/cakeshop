@extends('layouts.admin')

@section('title', __('Serviceable pincodes'))

@section('content')
    <x-admin-flash-swal />

    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="mb-2 inline-flex text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('← Settings') }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Serviceable pincodes') }}</h1>
            <p class="mt-1 max-w-xl text-sm text-gray-500">{{ __('Manage pincodes where delivery is available. Customers must enter a serviceable pincode before placing a delivery order.') }}</p>
        </div>
        @can('create', \App\Models\ServiceablePincode::class)
            <a href="{{ route('admin.serviceable-pincodes.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                {{ __('Add pincode') }}
            </a>
        @endcan
    </header>

    <form method="get" action="{{ route('admin.serviceable-pincodes.index') }}" class="mb-6 flex flex-wrap items-end gap-4">
        <div class="min-w-[12rem] flex-1">
            <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
            <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Pincode, locality, city…') }}" class="block w-full" />
        </div>
        <div>
            <label for="is_active" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
            <select name="is_active" id="is_active" class="block w-full rounded-lg border border-gray-300 px-3 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                <option value="">{{ __('All') }}</option>
                <option value="1" @selected(request('is_active') === '1')>{{ __('Active') }}</option>
                <option value="0" @selected(request('is_active') === '0')>{{ __('Inactive') }}</option>
            </select>
        </div>
        <x-button type="submit" variant="secondary">{{ __('Filter') }}</x-button>
        @if(request()->hasAny(['search', 'is_active', 'city']))
            <a href="{{ route('admin.serviceable-pincodes.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Clear') }}</a>
        @endif
    </form>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Pincode') }}</x-table.th>
                <x-table.th>{{ __('Locality') }}</x-table.th>
                <x-table.th>{{ __('City') }}</x-table.th>
                <x-table.th>{{ __('State') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($pincodes as $row)
                    <x-table.row>
                        <x-table.cell class="font-mono font-medium text-gray-900">{{ $row->pincode }}</x-table.cell>
                        <x-table.cell>{{ $row->locality ?: '—' }}</x-table.cell>
                        <x-table.cell>{{ $row->city }}</x-table.cell>
                        <x-table.cell>{{ $row->state }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$row->is_active ? 'success' : 'default'">{{ $row->is_active ? __('Active') : __('Inactive') }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('update', $row)
                                <a href="{{ route('admin.serviceable-pincodes.edit', $row) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                            @endcan
                            @can('delete', $row)
                                <span class="mx-2 text-gray-300">|</span>
                                <x-admin-delete-form
                                    :action="route('admin.serviceable-pincodes.destroy', $row)"
                                    :title="__('Delete this pincode?')"
                                />
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="6" class="py-12 text-center">
                            <p class="text-gray-500">{{ __('No serviceable pincodes yet.') }}</p>
                            @can('create', \App\Models\ServiceablePincode::class)
                                <a href="{{ route('admin.serviceable-pincodes.create') }}" class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Add your first pincode') }}</a>
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
        @if($pincodes->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $pincodes->links() }}</div>
        @endif
    </x-card>
@endsection
