@extends('layouts.admin')

@section('title', __('Add Product'))

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Add Product') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Create a new product for your store') }}</p>
    </header>

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">{{ __('Please fix the errors below.') }}</p>
        </div>
    @endif

    <x-card class="max-w-3xl" :elevated="true">
        <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-8" novalidate>
            @csrf
            @include('admin.products._form', ['product' => null, 'categories' => $categories, 'weightValues' => $weightValues])
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary" class="shadow-sm">{{ __('Create Product') }}</x-button>
                <a href="{{ route('admin.products.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                    {{ __('Cancel') }}
                </a>
            </div>
        </form>
    </x-card>
@endsection
