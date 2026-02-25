@extends('layouts.admin')

@section('title', __('Testimonials'))

@section('content')
    <header class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ __('Testimonials') }}</h1>
            <p class="mt-1 text-sm text-gray-500">{{ __('Manage customer reviews') }}</p>
        </div>
        @can('testimonials.create')
            <a href="{{ route('admin.testimonials.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-md transition duration-200 hover:bg-indigo-700 hover:shadow-lg focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                {{ __('Add Testimonial') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.testimonials.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700">{{ __('Search') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Name or review…') }}" class="block w-full" />
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
                <a href="{{ route('admin.testimonials.index') }}" class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Customer') }}</x-table.th>
                <x-table.th>{{ __('Review') }}</x-table.th>
                <x-table.th>{{ __('Rating') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($testimonials as $testimonial)
                    <x-table.row>
                        <x-table.cell>
                            <span class="font-medium text-gray-900">{{ $testimonial->customer_name }}</span>
                            @if($testimonial->customer_initials)
                                <span class="text-gray-500">({{ $testimonial->customer_initials }})</span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="max-w-xs text-gray-600">{{ Str::limit($testimonial->review, 80) }}</x-table.cell>
                        <x-table.cell>{{ $testimonial->rating }}/5</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$testimonial->is_active ? 'success' : 'default'">{{ $testimonial->is_active ? __('Active') : __('Inactive') }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('testimonials.update')
                                <a href="{{ route('admin.testimonials.edit', $testimonial) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                <span class="mx-2 text-gray-300">|</span>
                            @endcan
                            @can('testimonials.delete')
                                <form method="post" action="{{ route('admin.testimonials.destroy', $testimonial) }}" class="inline" onsubmit="return confirm('{{ __('Delete this testimonial?') }}');">
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
                            <p class="text-sm text-gray-500">{{ __('No testimonials yet.') }}</p>
                            @can('testimonials.create')
                                <a href="{{ route('admin.testimonials.create') }}" class="mt-2 inline-flex text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __('Add Testimonial') }}</a>
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($testimonials->hasPages())
        <div class="mt-4">
            {{ $testimonials->links() }}
        </div>
    @endif
@endsection
