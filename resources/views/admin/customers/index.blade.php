@extends('layouts.admin')

@section('title', __('Customers'))

@section('content')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Customers') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Storefront customers — separate from staff users.') }}</p>
        </div>
        @can('customers.create')
            <a href="{{ route('admin.customers.create') }}" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 font-medium text-white hover:bg-gray-700">
                {{ __('Add customer') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.customers.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Name, email, or phone…') }}" class="block w-full" />
            </div>
            <div class="w-48">
                <label for="status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 px-3 py-2">
                    <option value="">{{ __('Active') }}</option>
                    <option value="deleted" @selected(request('status') === 'deleted')>{{ __('Deleted (awaiting purge)') }}</option>
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 font-medium text-white hover:bg-gray-700">{{ __('Filter') }}</button>
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Name') }}</x-table.th>
                <x-table.th>{{ __('Email') }}</x-table.th>
                <x-table.th>{{ __('Phone') }}</x-table.th>
                <x-table.th>{{ __('Orders') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($customers as $customer)
                    <x-table.row>
                        <x-table.cell>{{ $customer->name }}</x-table.cell>
                        <x-table.cell>
                            @if($customer->email)
                                {{ $customer->email }}
                            @else
                                <x-badge variant="warning">{{ __('No email yet') }}</x-badge>
                            @endif
                        </x-table.cell>
                        <x-table.cell>{{ $customer->phone ?? '—' }}</x-table.cell>
                        <x-table.cell>{{ $customer->orders_count }}</x-table.cell>
                        <x-table.cell class="text-right">
                            <a href="{{ route('admin.customers.show', $customer) }}" class="text-gray-600 hover:text-gray-900">{{ __('View') }}</a>
                            @if(! $customer->trashed() && auth()->user()->can('customers.impersonate'))
                                <span class="mx-2 text-gray-300">|</span>
                                <form method="post" action="{{ route('admin.customers.impersonate', $customer) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-indigo-600 hover:text-indigo-800">{{ __('Shop as customer') }}</button>
                                </form>
                            @endif
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="text-center text-gray-500">{{ __('No customers found.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($customers->hasPages())
        <div class="mt-4">{{ $customers->links() }}</div>
    @endif
@endsection
