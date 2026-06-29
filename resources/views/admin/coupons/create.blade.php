@extends('layouts.admin')

@section('title', __('Add Coupon'))

@push('scripts')
    @vite('resources/js/admin-coupon-form.js')
@endpush

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Add Coupon') }}</h1>
    </header>

    <x-card class="max-w-3xl" :elevated="true">
        <form method="post" action="{{ route('admin.coupons.store') }}" class="space-y-6" data-coupon-form>
            @csrf
            <x-form-errors :show-validation-summary="true" />
            @include('admin.coupons._form')
            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary">{{ __('Create Coupon') }}</x-button>
                <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
@endsection
