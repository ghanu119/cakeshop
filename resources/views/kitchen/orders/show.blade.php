@extends('layouts.admin')

@section('title', ($readOnly ? __('Upcoming order') : __("Today's order")) . ' #' . $order->order_no)

@section('content')
    @php
        $readOnly = $readOnly ?? false;
        $statusReadOnly = $statusReadOnly ?? false;
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $orderedAt = $order->ordered_at?->setTimezone($tz);
        $deliveryAt = $order->delivery_at?->setTimezone($tz);
        $paymentPending = !$order->isPaymentVerified();
        $backRoute = $readOnly ? route('admin.kitchen.orders.upcoming') : route('admin.kitchen.orders.index');
        $backLabel = $readOnly ? __('Back to Upcoming Orders') : __("Back to Today's Orders");
    @endphp

    <div class="mx-auto max-w-6xl">
        {{-- Page Header --}}
        <div class="mb-6">
            <a href="{{ $backRoute }}" class="mb-4 inline-flex items-center text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ $backLabel }}
            </a>

            @if($readOnly)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ __('Read-only — status can be updated on the delivery day when the order is set to Processing.') }}
                </div>
            @elseif($statusReadOnly)
                <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                    {{ __('Awaiting administrator — status can be updated after Processing is set with a preparation time.') }}
                </div>
            @endif
            
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-gray-900">
                        {{ __('Order') }} <span class="font-normal text-gray-500">#{{ $order->order_no }}</span>
                    </h1>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ __('Placed on') }} {{ $orderedAt?->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>

                <div class="w-full shrink-0 md:w-auto">
                    @if($paymentPending)
                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-amber-200 bg-amber-50 px-3 py-1.5 text-sm font-semibold text-amber-700 shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            {{ __('Payment Pending') }}
                        </span>
                    @else
                        @if($readOnly)
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ __('Payment Verified') }}
                            </span>
                        @else
                            @can('orders.update')
                                @if($statusReadOnly)
                                    <div class="flex flex-wrap items-center justify-end gap-3">
                                        <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            {{ __('Payment Verified') }}
                                        </span>
                                        <span class="inline-flex items-center rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm font-semibold capitalize text-amber-800">
                                            {{ __($order->order_status) }}
                                        </span>
                                    </div>
                                @else
                                    @include('admin.orders.partials._status-form', [
                                        'order' => $order,
                                        'preparationRules' => $preparationRules,
                                        'statusFormAction' => route('admin.kitchen.orders.update-status', $order),
                                        'canSetPreparationTime' => false,
                                        'paymentBadge' => 'verified',
                                    ])
                                @endif
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-sm font-semibold text-emerald-700 shadow-sm">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    {{ __('Payment Verified') }}
                                </span>
                            @endcan
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Left Column (Main Details) --}}
            <div class="space-y-6 lg:col-span-8">

                {{-- Order Details Card --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Order Information') }}</h3>
                    </div>
                    <div class="p-6">
                        @include('admin.orders.partials._product-image-preview', [
                            'order' => $order,
                            'prominent' => true,
                        ])

                        {{-- Cake Customization --}}
                        @if($order->message_on_cake || $order->instructions)
                            <div class="border-t border-gray-100 pt-6">
                                <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('Cake Customization') }}</h4>
                                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                    @if($order->message_on_cake)
                                        <div>
                                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('Message on cake') }}</p>
                                            <div class="rounded-lg border border-amber-100 bg-amber-50 p-4">
                                                <p class="font-medium text-gray-800">"{{ $order->message_on_cake }}"</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if($order->instructions)
                                        <div>
                                            <p class="mb-2 text-sm font-medium text-gray-500">{{ __('Instructions') }}</p>
                                            <div class="rounded-lg border border-blue-100 bg-blue-50 p-4">
                                                <p class="font-medium text-gray-800">{{ $order->instructions }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- Right Column (Sidebar) --}}
            <div class="space-y-6 lg:col-span-4">

                @if(!$readOnly)
                    @include('admin.orders.partials._preparation-highlight', ['order' => $order, 'tz' => $tz])
                @endif

                {{-- Order Summary Card --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Order Summary') }}</h3>
                    </div>
                    <div class="p-6">
                        <div class="mb-6 flex items-start justify-between gap-4">
                            <div class="flex items-center gap-4">
                                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-500">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18zm-3-9v-2a2 2 0 00-2-2H8a2 2 0 00-2 2v2h12z"/></svg>
                                </div>
                                <div>
                                    <p class="mb-0.5 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Product') }}</p>
                                    <p class="font-bold text-gray-900 text-indigo-900">{{ $order->displayProductName() }}</p>
                                    @include('order.partials._order-options', [
                                        'order' => $order,
                                        'weightClass' => 'mt-2 text-base font-bold text-amber-800',
                                        'flavorClass' => 'mt-2 text-base font-bold text-rose-800',
                                    ])
                                </div>
                            </div>
                            @include('admin.orders.partials._product-image-thumb', ['order' => $order])
                        </div>

                        <div class="space-y-3 border-t border-gray-100 pt-4 text-sm">
                            <div class="flex items-center">
                                <div class="w-40 shrink-0 text-gray-500">{{ __('Quantity') }}</div>
                                <div class="font-medium text-gray-900">
                                    <span class="inline-flex items-center justify-center rounded-md bg-indigo-50 px-2.5 py-0.5 text-base font-bold text-indigo-700 ring-1 ring-inset ring-indigo-600/20">{{ $order->quantity }}</span>
                                </div>
                            </div>
                            <div class="flex items-center">
                                <div class="w-40 shrink-0 text-gray-500">{{ __('Order Created At') }}</div>
                                <div class="font-medium text-gray-900">{{ $orderedAt?->format('M d, Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <x-image-lightbox />
@endsection

@push('scripts')
    @vite('resources/js/image-lightbox.js')
@endpush