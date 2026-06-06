@extends('layouts.admin')

@section('title', __('Upcoming orders'))

@section('content')
    @php
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $isAdmin = auth()->user()->hasRole('Admin');
        $tableColspan = $isAdmin ? 8 : 7;
    @endphp
    <header class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __('Upcoming orders') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-gray-500">
                {{ __('Payment-verified orders scheduled after today. Status can be updated on the delivery day when the order is set to Processing.') }}
            </p>
            @if($orders->total() > 0)
                <p class="mt-2 text-sm font-medium text-gray-700">
                    {{ trans_choice(':count upcoming order|:count upcoming orders', $orders->total(), ['count' => $orders->total()]) }}
                </p>
            @endif
        </div>
        <a
            href="{{ route('admin.kitchen.orders.index') }}"
            class="inline-flex shrink-0 items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:bg-gray-50"
        >
            {{ __("Today's orders") }} →
        </a>
    </header>

    <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        {{ __('These orders are read-only until their delivery day. Open an order to review cake details and customization.') }}
    </div>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th class="w-16">{{ __('Image') }}</x-table.th>
                <x-table.th>{{ __('Product') }}</x-table.th>
                <x-table.th>{{ __('Order no') }}</x-table.th>
                <x-table.th>{{ __('Guest') }}</x-table.th>
                <x-table.th>{{ __('Due in') }}</x-table.th>
                @role('Admin')
                    <x-table.th>{{ __('Delivery at') }}</x-table.th>
                @endrole
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="min-w-[11rem] text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($orders as $order)
                    @php
                        $deliveryAt = $order->delivery_at?->setTimezone($tz);
                        $statusVariant = match($order->order_status) {
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'default',
                        };
                    @endphp
                    <x-table.row>
                        <x-table.cell class="w-16">
                            @include('admin.orders.partials._product-image-thumb', ['order' => $order])
                        </x-table.cell>
                        <x-table.cell>
                            <div class="font-medium text-gray-900">{{ $order->displayProductName() }}</div>
                            @include('order.partials._order-options', [
                                'order' => $order,
                                'weightClass' => 'text-xs text-amber-700',
                                'flavorClass' => 'text-xs text-rose-700',
                            ])
                            <div class="mt-1 text-xs text-gray-500">{{ __('Qty') }}: {{ $order->quantity }}</div>
                        </x-table.cell>
                        <x-table.cell class="font-mono text-sm">{{ $order->order_no }}</x-table.cell>
                        <x-table.cell>
                            <div>{{ $order->guest_name }}</div>
                            <div class="text-sm text-gray-500">{{ $order->guest_phone }}</div>
                        </x-table.cell>
                        <x-table.cell>
                            @include('kitchen.orders.partials._days-until-delivery-badge', ['order' => $order, 'size' => 'lg'])
                        </x-table.cell>
                        @role('Admin')
                            <x-table.cell>
                                @if($deliveryAt)
                                    <div class="font-medium text-gray-900">{{ $deliveryAt->format('D, j M Y') }}</div>
                                    <div class="text-sm text-gray-500">{{ $deliveryAt->format('h:i A') }}</div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $deliveryAt->diffForHumans() }}</div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </x-table.cell>
                        @endrole
                        <x-table.cell>
                            <x-badge :variant="$statusVariant">{{ $order->order_status }}</x-badge>
                        </x-table.cell>
                        <x-table.cell class="min-w-[11rem] whitespace-nowrap text-right">
                            <a
                                href="{{ route('admin.kitchen.orders.upcoming.show', $order) }}"
                                class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-200 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                {{ __('View details') }}
                            </a>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="{{ $tableColspan }}" class="text-center text-gray-500">
                            {{ __('No upcoming verified orders.') }}
                        </x-table.cell>
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

    <x-image-lightbox />
@endsection

@push('scripts')
    @vite('resources/js/image-lightbox.js')
@endpush
