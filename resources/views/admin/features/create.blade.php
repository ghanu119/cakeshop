@extends('layouts.admin')

@section('title', __('Add Feature'))

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Add Feature') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Add a “Why Choose Us” feature for the home page') }}</p>
    </header>

    <x-card class="max-w-2xl" :elevated="true">
        <form method="post" action="{{ route('admin.features.store') }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            <x-form-errors :show-validation-summary="true" />
            @include('admin.features._form', ['feature' => null])
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary" class="shadow-sm">{{ __('Create Feature') }}</x-button>
                <a href="{{ route('admin.features.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
