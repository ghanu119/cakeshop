@extends('layouts.admin')

@section('title', __('Add User'))

@section('content')
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Add User') }}</h1>
    </header>

    <x-card class="max-w-xl">
        <form method="post" action="{{ route('admin.users.store') }}" class="space-y-6">
            @csrf
            @include('admin.users._form', ['user' => null])
            <div class="flex gap-4">
                <x-button type="submit" variant="primary">{{ __('Create User') }}</x-button>
                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
