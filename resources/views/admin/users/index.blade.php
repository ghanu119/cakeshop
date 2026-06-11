@extends('layouts.admin')

@section('title', __('Users'))

@section('content')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Users') }}</h1>
        @can('users.create')
            <a href="{{ route('admin.users.create') }}" class="inline-flex items-center rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                {{ __('Add User') }}
            </a>
        @endcan
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search (name, email, phone)') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search…') }}" class="block w-full" />
            </div>
            <div class="w-40">
                <label for="role" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Role') }}</label>
                <select name="role" id="role" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(request('role') === $role->name)>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'role']))
                <a href="{{ route('admin.users.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Name') }}</x-table.th>
                <x-table.th>{{ __('Email') }}</x-table.th>
                <x-table.th>{{ __('Phone') }}</x-table.th>
                <x-table.th>{{ __('Roles') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($users as $user)
                    <x-table.row>
                        <x-table.cell>{{ $user->name }}</x-table.cell>
                        <x-table.cell>{{ $user->email }}</x-table.cell>
                        <x-table.cell>{{ $user->phone ?? '—' }}</x-table.cell>
                        <x-table.cell>
                            @foreach($user->roles as $role)
                                <x-badge variant="default" class="mr-1">{{ $role->name }}</x-badge>
                            @endforeach
                            @if($user->roles->isEmpty())
                                —
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @can('users.update')
                                <a href="{{ route('admin.users.edit', $user) }}" class="text-gray-600 hover:text-gray-900">{{ __('Edit') }}</a>
                                @if($user->id !== auth()->id())
                                    <span class="mx-2 text-gray-300">|</span>
                                @endif
                            @endcan
                            @can('users.delete')
                                @if($user->id !== auth()->id())
                                    <x-admin-delete-form
                                        :action="route('admin.users.destroy', $user)"
                                        :title="__('Delete this user?')"
                                    />
                                @endif
                            @endcan
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="5" class="text-center text-gray-500">{{ __('No users found.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($users->hasPages())
        <div class="mt-4">
            {{ $users->links() }}
        </div>
    @endif
@endsection
