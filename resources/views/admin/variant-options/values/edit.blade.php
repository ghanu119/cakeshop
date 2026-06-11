@extends('layouts.admin')

@section('title', __('Edit value'))

@section('content')
    <header class="mb-6"><h1 class="text-2xl font-semibold text-gray-900">{{ __('Edit value') }}</h1></header>
    <x-card>
        <form method="post" action="{{ route('admin.variant-option-types.values.update', [$type, $value]) }}" class="space-y-4">
            @csrf
            <x-form-errors :show-validation-summary="true" />
            @method('PUT')
            @include('admin.variant-options.values._form', ['type' => $type, 'value' => $value])
            <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
        </form>
    </x-card>
@endsection
