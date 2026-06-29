@extends('layouts.admin')

@section('title', __('Edit Coupon'))

@push('scripts')
    @vite('resources/js/admin-coupon-form.js')
@endpush

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ __('Edit Coupon') }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ $coupon->code }}</p>
    </header>

    <x-card class="max-w-3xl" :elevated="true">
        <form method="post" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-6" data-coupon-form>
            @csrf
            @method('PUT')
            <x-form-errors :show-validation-summary="true" />
            @include('admin.coupons._form')
            <div class="flex flex-wrap gap-3 border-t border-gray-200 pt-6">
                <x-button type="submit" variant="primary">{{ __('Update Coupon') }}</x-button>
                <a href="{{ route('admin.coupons.index') }}" class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ __('Cancel') }}</a>
            </div>
        </form>
    </x-card>
@endsection
