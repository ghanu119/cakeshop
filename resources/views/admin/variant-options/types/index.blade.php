@extends('layouts.admin')

@section('title', __('Variant option types'))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.settings.index') }}" class="mb-2 inline-flex text-sm text-gray-500 hover:text-gray-700">{{ __('← Settings') }}</a>
            <h1 class="text-2xl font-semibold text-gray-900">{{ __('Variant option types') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Weight, flavor, toppings, etc.') }}</p>
        </div>
        <a href="{{ route('admin.variant-option-types.create') }}" class="inline-flex rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">{{ __('Add type') }}</a>
    </header>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Name') }}</x-table.th>
                <x-table.th>{{ __('Slug') }}</x-table.th>
                <x-table.th>{{ __('Values') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($types as $type)
                    <x-table.row>
                        <x-table.cell>{{ $type->name_en }}</x-table.cell>
                        <x-table.cell><code>{{ $type->slug }}</code></x-table.cell>
                        <x-table.cell>{{ $type->values_count }}</x-table.cell>
                        <x-table.cell><x-badge :variant="$type->isActive() ? 'success' : 'default'">{{ $type->status }}</x-badge></x-table.cell>
                        <x-table.cell class="text-right space-x-2">
                            <a href="{{ route('admin.variant-option-types.values.index', $type) }}" class="text-indigo-600 hover:underline text-sm">{{ __('Values') }}</a>
                            <a href="{{ route('admin.variant-option-types.edit', $type) }}" class="text-indigo-600 hover:underline text-sm">{{ __('Edit') }}</a>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row><x-table.cell colspan="5" class="text-center text-gray-500">{{ __('No types yet.') }}</x-table.cell></x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
        @if($types->hasPages())<div class="border-t px-4 py-3">{{ $types->links() }}</div>@endif
    </x-card>
@endsection
