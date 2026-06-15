@extends('layouts.admin')

@section('title', __('Add customer'))

@section('content')
    <header class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Add customer') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Create a new storefront customer.') }}</p>
    </header>

    <x-card class="max-w-xl">
        <div class="space-y-6">
            <form
                id="customer-create-form"
                method="post"
                action="{{ route('admin.customers.store') }}"
                class="space-y-6"
                data-customer-create-form
                data-lookup-url="{{ route('admin.customers.lookup') }}"
            >
                @csrf
                <x-form-errors :show-validation-summary="true" />
                @include('admin.customers._form')
            </form>

            @include('admin.customers._lookup-panel', ['lookup' => $lookup ?? ['conflict' => false, 'match' => null]])

            @php
                $disableCreate = ($lookup['match'] ?? null) || ($lookup['conflict'] ?? false);
            @endphp
            <div class="flex gap-4">
                <x-button type="submit" form="customer-create-form" variant="primary" id="customer-create-submit" :disabled="$disableCreate">{{ __('Create customer') }}</x-button>
                <a href="{{ route('admin.customers.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </div>
    </x-card>
@endsection

@push('scripts')
    @vite(['resources/js/admin-customer-lookup.js'])
@endpush
