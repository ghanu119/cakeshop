@extends('layouts.admin')

@section('title', __('Edit cake weight'))

@section('content')
    <header class="mb-8">
        <a href="{{ route('admin.cake-weights.index') }}" class="mb-2 inline-flex text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('← Cake weights') }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Edit cake weight') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $weight->label }}</p>
    </header>

    <x-card class="max-w-xl" :elevated="true">
        <form method="post" action="{{ route('admin.cake-weights.update', $weight) }}" class="space-y-8" novalidate>
            @csrf
            <x-form-errors :show-validation-summary="true" />
            @method('PUT')
            @include('admin.cake-weights._form', ['weight' => $weight])
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary">{{ __('Update weight') }}</x-button>
                <a href="{{ route('admin.cake-weights.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
@endsection
