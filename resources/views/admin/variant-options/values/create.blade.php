@extends('layouts.admin')

@section('title', __('Add value'))

@section('content')
    <header class="mb-6"><h1 class="text-2xl font-semibold text-gray-900">{{ __('Add value') }} — {{ $type->name_en }}</h1></header>
    <x-card>
        <form method="post" action="{{ route('admin.variant-option-types.values.store', $type) }}" class="space-y-4">
            @csrf
            @include('admin.variant-options.values._form', ['type' => $type, 'value' => null])
            <x-button type="submit" variant="primary">{{ __('Create') }}</x-button>
        </form>
    </x-card>
@endsection
