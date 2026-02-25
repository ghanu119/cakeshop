@extends('layouts.admin')

@section('title', __('Contact enquiries'))

@section('content')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Contact enquiries') }}</h1>
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.contact-enquiries.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search (name, email, subject)') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search…') }}" class="block w-full" />
            </div>
            <div class="w-40">
                <label for="from_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('From date') }}</label>
                <x-input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="block w-full" />
            </div>
            <div class="w-40">
                <label for="to_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('To date') }}</label>
                <x-input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="block w-full" />
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'from_date', 'to_date']))
                <a href="{{ route('admin.contact-enquiries.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Date') }}</x-table.th>
                <x-table.th>{{ __('Name') }}</x-table.th>
                <x-table.th>{{ __('Email') }}</x-table.th>
                <x-table.th>{{ __('Subject') }}</x-table.th>
                <x-table.th>{{ __('Message') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($enquiries as $enquiry)
                    <x-table.row>
                        <x-table.cell class="whitespace-nowrap text-sm text-gray-500">{{ $enquiry->created_at->format('d M Y H:i') }}</x-table.cell>
                        <x-table.cell>{{ $enquiry->name }}</x-table.cell>
                        <x-table.cell>{{ $enquiry->email }}</x-table.cell>
                        <x-table.cell>{{ $enquiry->subject }}</x-table.cell>
                        <x-table.cell class="max-w-xs truncate text-gray-600">{{ Str::limit($enquiry->message, 60) }}</x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="text-center text-gray-500">{{ __('No enquiries found.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($enquiries->hasPages())
        <div class="mt-4">
            {{ $enquiries->links() }}
        </div>
    @endif
@endsection
