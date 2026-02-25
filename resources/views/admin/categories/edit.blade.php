@extends('layouts.admin')

@section('title', __('Edit Category'))

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Edit Category') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $category->name_en }}</p>
    </header>

    <x-card class="max-w-xl" :elevated="true">
        <form method="post" action="{{ route('admin.categories.update', $category) }}" class="space-y-8">
            @csrf
            @method('PUT')
            @include('admin.categories._form', ['category' => $category])
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary" class="shadow-sm">{{ __('Update Category') }}</x-button>
                <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
