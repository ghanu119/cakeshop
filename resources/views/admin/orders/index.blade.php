@extends('layouts.admin')

@section('title', __('Orders'))

@section('content')
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Orders') }}</h1>
    </header>

    <x-card class="mb-6">
        <form method="get" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="min-w-[200px] flex-1">
                <label for="search" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Search (phone, name, UUID)') }}</label>
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
                </select>
            </div>
            <div class="w-40">
                <label for="payment_status" class="mb-1 block text-sm font-medium text-gray-700">{{ __('Payment') }}</label>
                <select name="payment_status" id="payment_status" class="block w-full rounded-lg border border-gray-300 px-3 py-2 shadow-sm focus:border-gray-500 focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                    <option value="">{{ __('All') }}</option>
                    <option value="pending" @selected(request('payment_status') === 'pending')>{{ __('Pending') }}</option>
                    <option value="verified" @selected(request('payment_status') === 'verified')>{{ __('Verified') }}</option>
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
            <button type="submit" class="rounded-lg bg-gray-800 px-4 py-2 font-medium text-white transition duration-200 hover:bg-gray-700">
                {{ __('Filter') }}
            </button>
            @if(request()->hasAny(['search', 'order_status', 'payment_status', 'from_date', 'to_date']))
                <a href="{{ route('admin.orders.index') }}" class="rounded-lg border border-gray-300 bg-white px-4 py-2 font-medium text-gray-700 transition duration-200 hover:bg-gray-50">
                    {{ __('Reset') }}
                </a>
            @endif
        </form>
    </x-card>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('UUID') }}</x-table.th>
                <x-table.th>{{ __('Guest') }}</x-table.th>
                <x-table.th>{{ __('Product') }}</x-table.th>
                <x-table.th>{{ __('Amount') }}</x-table.th>
                <x-table.th>{{ __('Payment') }}</x-table.th>
                <x-table.th>{{ __('Order status') }}</x-table.th>
                <x-table.th>{{ __('Ordered at') }}</x-table.th>
                <x-table.th class="text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @php
                    $tz = settings('timezone') ?? 'Asia/Kolkata';
                @endphp
                @forelse($orders as $order)
                    <x-table.row>
                        <x-table.cell class="font-mono text-sm">{{ $order->uuid }}</x-table.cell>
                        <x-table.cell>
                            <div>{{ $order->guest_name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->guest_phone }}</div>
                        </x-table.cell>
                        <x-table.cell>
                            <div>{{ $order->displayProductName() }}</div>
                            @include('order.partials._order-options', [
                                'order' => $order,
                                'weightClass' => 'text-xs text-amber-700',
                                'flavorClass' => 'text-xs text-rose-700',
                            ])
                        </x-table.cell>
                        <x-table.cell class="whitespace-nowrap font-medium">₹ {{ number_format($order->amount, 2) }}</x-table.cell>
                        <x-table.cell>
                            <x-badge :variant="$order->payment_status === 'verified' ? 'success' : 'warning'">{{ $order->payment_status }}</x-badge>
                        </x-table.cell>
                        <x-table.cell>
                            @php
                                $statusVariant = match($order->order_status) {
                                    'pending' => 'warning',
                                    'processing' => 'info',
                                    'completed' => 'success',
                                    'cancelled' => 'danger',
                                    default => 'default',
                                };
                            @endphp
                            <x-badge :variant="$statusVariant">{{ $order->order_status }}</x-badge>
                        </x-table.cell>
                        <x-table.cell>{{ $order->ordered_at?->setTimezone($tz)->format('d M Y H:i') }}</x-table.cell>
                        <x-table.cell class="whitespace-nowrap text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-3 py-1.5 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-100">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                {{ __('View') }}
                            </a>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="8" class="text-center text-gray-500">{{ __('No orders found.') }}</x-table.cell>
                    </x-table.row>
                @endforelse
            </x-table.body>
        </x-table.wrapper>
    </x-card>

    @if($orders->hasPages())
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    @endif
@endsection
