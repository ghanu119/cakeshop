@extends('layouts.admin')

@section('title', __('Coupons'))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Coupons') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage discount coupons for checkout') }}</p>
        </div>
        @can('coupons.create')
            <a href="{{ route('admin.coupons.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-indigo-700">
                {{ __('Add Coupon') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.coupons.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Code or label…') }}" class="block w-full" />
            </div>
            <div class="w-44">
                <label for="status" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Status') }}</label>
                <select name="status" id="status" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="active" @selected(request('status') === 'active')>{{ __('Active') }}</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>{{ __('Inactive') }}</option>
                </select>
            </div>
            <div class="w-44">
                <label for="auto_apply" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Auto apply') }}</label>
                <select name="auto_apply" id="auto_apply" class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="1" @selected(request('auto_apply') === '1')>{{ __('Yes') }}</option>
                    <option value="0" @selected(request('auto_apply') === '0')>{{ __('No') }}</option>
                </select>
            </div>
            <x-button type="submit" variant="primary">{{ __('Filter') }}</x-button>
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Code') }}</x-table.th>
                <x-table.th>{{ __('Label') }}</x-table.th>
                <x-table.th>{{ __('Discount') }}</x-table.th>
                <x-table.th>{{ __('Valid') }}</x-table.th>
                <x-table.th>{{ __('Auto') }}</x-table.th>
                <x-table.th>{{ __('Secret') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($coupons as $coupon)
                    <x-table.row>
                        <x-table.cell><span class="font-mono font-medium">{{ $coupon->code }}</span></x-table.cell>
                        <x-table.cell>{{ $coupon->label }}</x-table.cell>
                        <x-table.cell>
                            @if($coupon->discount_type === 'percentage')
                                {{ number_format($coupon->discount_amount, 0) }}% (max {{ number_format($coupon->max_discount_amount ?? 0, 2) }})
                            @else
                                {{ number_format($coupon->discount_amount, 2) }}
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-sm text-gray-600">
                            {{ $coupon->from_date->format('d M Y') }} – {{ $coupon->to_date->format('d M Y') }}
                        </x-table.cell>
                        <x-table.cell>
                            @if($coupon->auto_apply)
                                <x-badge variant="success">{{ __('Yes') }}</x-badge>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @if($coupon->is_secret)
                                <x-badge variant="warning">{{ __('Yes') }}</x-badge>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$coupon->status === 'active' ? 'success' : 'default'">
                                {{ $coupon->status === 'active' ? __('Active') : __('Inactive') }}
                            </x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('coupons.update')
                                <a href="{{ route('admin.coupons.edit', $coupon) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('coupons.delete')
                                <x-admin-delete-form :action="route('admin.coupons.destroy', $coupon)" :title="__('Delete this coupon?')" />
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="7" class="py-12 text-center text-gray-500">{{ __('No coupons yet.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($coupons->hasPages())
        <div class="mt-4">{{ $coupons->links() }}</div>
    @endif
@endsection
