@extends('layouts.admin')

@section('title', $showTodayChrome ? __("Today's orders") : __('Orders'))

@section('content')
    <header data-highlight-target="{{ $showTodayChrome ? 'deliveries_today' : '' }}" @class([
        'mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between',
        'notification-highlight rounded-2xl px-4 py-3' => $showTodayChrome && in_array('deliveries_today', ($unreadHighlightTargets ?? collect())->toArray(), true),
    ])>
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ $showTodayChrome ? __("Today's orders") : __('Orders') }}</h1>
            @if($showTodayChrome)
                <p class="mt-1 text-sm text-gray-500">{{ __('All orders scheduled for today — every type, payment state, and status.') }}</p>
            @endif
        </div>
    </header>

    @unless($showTodayChrome)
    <x-card class="mb-6">
        <form id="admin-orders-filter-form" method="get" action="{{ route('admin.orders.index') }}" class="space-y-4">
            @if(request('sort'))
                <input type="hidden" name="sort" value="{{ request('sort') }}" />
            @endif
            @if(request('direction'))
                <input type="hidden" name="direction" value="{{ request('direction') }}" />
            @endif
            <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search (phone, name, order no)') }}</label>
                <x-input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="{{ __('Search…') }}" class="block w-full" />
            </div>
            <div class="w-40">
                <label for="order_status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Order status') }}</label>
                <select name="order_status" id="order_status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="pending" @selected(request('order_status') === 'pending')>{{ __('Pending') }}</option>
                    <option value="processing" @selected(request('order_status') === 'processing')>{{ __('Processing') }}</option>
                    <option value="completed" @selected(request('order_status') === 'completed')>{{ __('Completed') }}</option>
                    <option value="cancelled" @selected(request('order_status') === 'cancelled')>{{ __('Cancelled') }}</option>
                    <option value="delivered" @selected(request('order_status') === 'delivered')>{{ __('Delivered') }}</option>
                </select>
            </div>
            <div class="w-40">
                <label for="payment_status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment') }}</label>
                <select name="payment_status" id="payment_status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="pending" @selected(request('payment_status') === 'pending')>{{ __('Pending') }}</option>
                    <option value="partially_paid" @selected(request('payment_status') === 'partially_paid')>{{ __('Partially paid') }}</option>
                    <option value="verified" @selected(request('payment_status') === 'verified')>{{ __('Verified') }}</option>
                    <option value="cash_on_store" @selected(request('payment_status') === 'cash_on_store')>{{ __('In-store') }}</option>
                    <option value="in_store_outstanding" @selected(request('payment_status') === 'in_store_outstanding')>{{ __('In-store — balance due') }}</option>
                </select>
            </div>
            <div class="w-40">
                <label for="from_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('From date') }}</label>
                <x-input type="date" name="from_date" id="from_date" value="{{ request('from_date') }}" class="block w-full" />
            </div>
            <div class="w-40">
                <label for="to_date" class="mb-1 block text-sm font-medium text-gray-700">{{ __('To date') }}</label>
                <x-input type="date" name="to_date" id="to_date" value="{{ request('to_date') }}" class="block w-full" />
            </div>
            <div class="flex flex-col gap-2">
                <span class="text-sm font-medium text-gray-700">{{ __('Quick filters') }}</span>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="delivery_today" value="1" @checked(request()->boolean('delivery_today')) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    {{ __('Delivery today') }}
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="awaiting_payment_verification" value="1" @checked(request()->boolean('awaiting_payment_verification')) class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                    {{ __('Awaiting payment verification') }}
                </label>
            </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 border-t border-gray-100 pt-3">
            <button type="submit" class="shrink-0 rounded-lg bg-gray-800 px-4 py-2 text-sm font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'order_status', 'payment_status', 'from_date', 'to_date', 'delivery_today', 'awaiting_payment_verification', 'sort', 'direction']))
                <a href="{{ route('admin.orders.index') }}" class="shrink-0 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
            @include('admin.orders.partials._in-store-payment-stats', ['paymentStats' => $paymentStats ?? null])
            </div>
        </form>
    </x-card>
    @endunless

    <div
        id="admin-orders-results"
        data-orders-url="{{ route('admin.orders.index') }}"
        data-today-view="{{ $isTodayEntry ? '1' : '0' }}"
    >
        @include('admin.orders.partials._list-results', [
            'orders' => $orders,
            'showTodayChrome' => $showTodayChrome,
            'todayListMode' => $todayListMode,
        ])
    </div>
@endsection

@push('scripts')
    @vite('resources/js/admin-orders-index.js')
@endpush
