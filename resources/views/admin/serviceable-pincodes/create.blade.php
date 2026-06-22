@extends('layouts.admin')

@section('title', __('Add serviceable pincode'))

@section('content')
    <header class="mb-8">
        <a href="{{ route('admin.serviceable-pincodes.index') }}" class="mb-2 inline-flex text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('← Serviceable pincodes') }}</a>
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Add serviceable pincode') }}</h1>
    </header>

    <x-card>
        <form method="post" action="{{ route('admin.serviceable-pincodes.store') }}" class="space-y-8">
            @csrf
            <x-form-errors />
            @include('admin.serviceable-pincodes._form', ['pincode' => $pincode])
            <div class="flex gap-4">
                <x-button type="submit" variant="primary">{{ __('Create pincode') }}</x-button>
                <a href="{{ route('admin.serviceable-pincodes.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
@endsection
