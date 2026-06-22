@extends('layouts.admin')

@section('title', __("Today's orders"))

@section('content')
    @php
        $tz = settings('timezone') ?? 'Asia/Kolkata';
    @endphp
    <header data-highlight-target="kitchen_today" class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between {{ in_array('kitchen_today', ($unreadHighlightTargets ?? collect())->toArray(), true) ? 'notification-highlight rounded-2xl px-4 py-3' : '' }}">
        <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ __("Today's orders") }}</h1>
    </header>

    <x-card :padding="false">
        <x-table.wrapper>
            <x-table.header>
                <x-table.th>{{ __('Order no') }}</x-table.th>
                @role('Admin')
                    <x-table.th>{{ __('Guest') }}</x-table.th>
                @endrole
                <x-table.th>{{ __('Product') }}</x-table.th>
                <x-table.th>{{ __('Quantity') }}</x-table.th>
                @role('Admin')
                    <x-table.th>{{ __('Delivery at') }}</x-table.th>
                @endrole
                <x-table.th>{{ __('Prepare by') }}</x-table.th>
                <x-table.th>{{ __('Status') }}</x-table.th>
                <x-table.th class="min-w-[11rem] text-right">{{ __('Actions') }}</x-table.th>
            </x-table.header>
            <x-table.body>
                @forelse($orders as $order)
                    <x-table.row>
                        <x-table.cell class="font-mono text-sm">{{ $order->order_no }}</x-table.cell>
                        @role('Admin')
                            <x-table.cell>
                                <div>{{ $order->guest_name }}</div>
                                <div class="text-sm text-gray-500">{{ $order->guest_phone }}</div>
                            </x-table.cell>
                        @endrole
                        <x-table.cell>
                            <div>{{ $order->displayProductName() }}</div>
                            @include('order.partials._order-options', [
                                'order' => $order,
                                'weightClass' => 'text-xs text-amber-700',
                                'flavorClass' => 'text-xs text-rose-700',
                            ])
                        </x-table.cell>
                        <x-table.cell>{{ $order->quantity }}</x-table.cell>
                        @role('Admin')
                            <x-table.cell>{{ $order->delivery_at?->setTimezone($tz)->format('d M Y H:i') }}</x-table.cell>
                        @endrole
                        <x-table.cell>
                            @php
                                $preparationAt = $order->preparation_at?->setTimezone($tz);
                                $prepOverdue = $preparationAt && $preparationAt->isPast() && $order->order_status === 'processing';
                            @endphp
                            @if($preparationAt)
                                <div class="font-medium {{ $prepOverdue ? 'text-red-700' : 'text-gray-900' }}">
                                    {{ $preparationAt->format('d M Y H:i') }}
                                </div>
                                @if($prepOverdue)
                                    <x-badge variant="danger" class="mt-1">{{ __('Overdue') }}</x-badge>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
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
                        <x-table.cell class="min-w-[11rem] whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a
                                    href="{{ route('admin.kitchen.orders.show', $order) }}"
                                    class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition duration-200 ease-in-out hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                >
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                    {{ __('View') }}
                                </a>
                                @role('Admin')
                                    <a
                                        href="{{ route('admin.orders.show', $order) }}?from=kitchen#order-status"
                                        class="inline-flex shrink-0 items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition duration-200 ease-in-out hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2"
                                    >
                                        {{ __('Change status') }}
                                    </a>
                                @endrole
                            </div>
                        </x-table.cell>
                    </x-table.row>
                @empty
                    <x-table.row>
                        <x-table.cell colspan="{{ auth()->user()->hasRole('Admin') ? 8 : 6 }}" class="text-center text-gray-500">
                            {{ __('No orders for today. Orders appear here after payment is verified, delivery is today, and status is set to Processing with a preparation time.') }}
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
@endsection
