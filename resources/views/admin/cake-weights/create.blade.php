@extends('layouts.admin')

@section('title', __('Add cake weight'))

@section('content')
    <header class="mb-8">
        <a href="{{ route('admin.cake-weights.index') }}" class="mb-2 inline-flex text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('← Cake weights') }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Add cake weight') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Create a new weight option for your cakes.') }}</p>
    </header>

    <x-card class="max-w-xl" :elevated="true">
        <form method="post" action="{{ route('admin.cake-weights.store') }}" class="space-y-8" novalidate>
            @csrf
            @include('admin.cake-weights._form', ['weight' => null])
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary">{{ __('Save weight') }}</x-button>
                <a href="{{ route('admin.cake-weights.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
@endsection
