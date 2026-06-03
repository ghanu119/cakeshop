@extends('layouts.admin')

@section('title', __('Edit option type'))

@section('content')
    <header class="mb-6"><h1 class="text-2xl font-semibold text-gray-900">{{ __('Edit option type') }}</h1></header>
    <x-card>
        <form method="post" action="{{ route('admin.variant-option-types.update', $type) }}" class="space-y-4">
            @csrf
            @method('PUT')
            @include('admin.variant-options.types._form', ['type' => $type])
            <x-button type="submit" variant="primary">{{ __('Update') }}</x-button>
        </form>
    </x-card>
@endsection
