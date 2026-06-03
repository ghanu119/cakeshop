@extends('layouts.admin')

@section('title', __('Values') . ' — ' . $type->name_en)

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.variant-option-types.index') }}" class="mb-2 inline-flex text-sm text-gray-500 hover:text-gray-700">{{ __('← Option types') }}</a>
            <h1 class="text-2xl font-semibold text-gray-900">{{ $type->name_en }} — {{ __('Values') }}</h1>
        </div>
        <a href="{{ route('admin.variant-option-types.values.create', $type) }}" class="inline-flex rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add value') }}</a>
    </header>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Label') }}</x-table.th>
                @if($type->slug === 'weight')<x-table.th>{{ __('Grams') }}</x-table.th>@endif
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th>{{ __('Sort') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($values as $value)
                    <x-table.row>
                        <x-table.cell>{{ $value->label }}</x-table.cell>
                        @if($type->slug === 'weight')<x-table.cell>{{ $value->grams ?? '—' }}</x-table.cell>@endif
                        <x-table.cell><x-badge :variant="$value->isActive() ? 'success' : 'default'">{{ $value->status }}</x-badge></x-table.cell>
                        <x-table.cell>{{ $value->sort_order }}</x-table.cell>
                        <x-table.cell class="text-right">
                            <a href="{{ route('admin.variant-option-types.values.edit', [$type, $value]) }}" class="text-indigo-600 hover:underline text-sm">{{ __('Edit') }}</a>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row><x-table.cell colspan="5" class="text-center text-gray-500">{{ __('No values yet.') }}</x-table.cell></x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
        @if($values->hasPages())<div class="border-t px-4 py-3">{{ $values->links() }}</div>@endif
    </x-card>
@endsection
