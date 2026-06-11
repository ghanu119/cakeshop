@extends('layouts.admin')

@section('title', __('Cake weights'))

@section('content')
    <x-admin-flash-swal />

    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="mb-2 inline-flex text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('← Settings') }}</a>
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Cake weights') }}</h1>
            <p class="mt-1 max-w-xl text-sm text-gray-500">{{ __('Manage the weight options customers can choose (e.g. 250 gm, 500 gm, 1 KG). Assign prices per weight on each product.') }}</p>
        </div>
        <a href="{{ route('admin.cake-weights.create') }}" class="inline-flex shrink-0 items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ __('Add weight') }}
        </a>
    </header>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Label') }}</x-table.th>
                <x-table.th>{{ __('Grams') }}</x-table.th>
                <x-table.th>{{ __('Sort') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($values as $weight)
                    <x-table.row>
                        <x-table.cell class="font-medium text-gray-900">{{ $weight->label }}</x-table.cell>
                        <x-table.cell>{{ number_format($weight->grams) }} g</x-table.cell>
                        <x-table.cell>{{ $weight->sort_order }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$weight->isActive() ? 'success' : 'default'">{{ $weight->isActive() ? __('Active') : __('Inactive') }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('settings.manage')
                                <a href="{{ route('admin.cake-weights.edit', $weight) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                                <x-admin-delete-form
                                    :action="route('admin.cake-weights.destroy', $weight)"
                                    :title="__('Delete this weight?')"
                                />
                            @else
                                <a href="{{ route('admin.cake-weights.edit', $weight) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Edit') }}</a>
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="py-12 text-center">
                            <p class="text-gray-500">{{ __('No weights yet.') }}</p>
                            <a href="{{ route('admin.cake-weights.create') }}" class="mt-3 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-800">{{ __('Add your first weight') }}</a>
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
        @if($values->hasPages())
            <div class="border-t border-gray-100 px-4 py-3">{{ $values->links() }}</div>
        @endif
    </x-card>
@endsection
