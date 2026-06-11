@extends('layouts.admin')

@section('title', __('Add option type'))

@section('content')
    <header class="mb-6"><h1 class="text-2xl font-semibold text-gray-900">{{ __('Add option type') }}</h1></header>
    <x-card>
        <form method="post" action="{{ route('admin.variant-option-types.store') }}" class="space-y-4">
            @csrf
            <x-form-errors :show-validation-summary="true" />
            @include('admin.variant-options.types._form', ['type' => null])
            <x-button type="submit" variant="primary">{{ __('Create') }}</x-button>
        </form>
    </x-card>
@endsection
