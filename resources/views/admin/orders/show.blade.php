@extends('layouts.admin')

@section('title', __('Order') . ' #' . $order->order_no)

@section('content')
    @php
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $orderedAt = $order->ordered_at?->setTimezone($tz);
        $deliveryAt = $order->delivery_at?->setTimezone($tz);
        $paymentPending = !$order->isPaymentVerified();
    @endphp

    <div class="mx-auto max-w-6xl">
        {{-- Banner for Pending Payment --}}
        @if($paymentPending)
            <div class="mb-6 flex flex-col justify-between gap-4 rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm sm:flex-row sm:items-center">
                <div class="flex items-start gap-3 sm:items-center">
                    <div class="rounded-full bg-amber-100 p-2 text-amber-600">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-amber-800">{{ __('Payment Action Required') }}</h3>
                        <p class="mt-0.5 text-sm text-amber-700">{{ __('Verify payment to enable order status updates.') }}</p>
                    </div>
                </div>
                @can('orders.update')
                    <form
                        method="post"
                        action="{{ route('admin.orders.verify-payment', $order) }}"
                        class="shrink-0"
                        data-verify-payment-form
                        data-confirm-title="{{ __('Verify payment?') }}"
                        data-confirm-message="{{ __('Are you sure you want to verify payment for this order?') }}"
                        data-confirm-yes="{{ __('Yes, verify') }}"
                        data-confirm-no="{{ __('Cancel') }}"
                    >
                        @csrf
                        <button type="submit" class="rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-700 disabled:cursor-not-allowed disabled:opacity-70">
                            <span data-submit-label>{{ __('Verify Payment') }}</span>
                            <span data-submitting-label class="hidden">{{ __('Verifying...') }}</span>
                        </button>
                    </form>
                @endcan
            </div>
        @endif

        {{-- Page Header --}}
        <div class="mb-6">
            <a href="{{ route('admin.orders.index') }}" class="mb-4 inline-flex items-center text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
                <svg class="mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('Back to Orders') }}
            </a>
            
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
                        @can('orders.update')
                            @include('admin.orders.partials._status-form', [
                                'order' => $order,
                                'preparationRules' => $preparationRules,
                                'statusFormAction' => route('admin.orders.update-status', $order),
                                'fromKitchen' => request()->query('from') === 'kitchen',
                                'paymentBadge' => 'verified',
                                'paymentBadgeLabel' => $order->adminPaymentStatusLabel(),
                                'paymentBadgeInStore' => $order->isCashOnStore(),
                            ])
                        @else
                            <span @class([
                                'inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-sm font-semibold shadow-sm',
                                'border-violet-200 bg-violet-50 text-violet-800' => $order->isCashOnStore(),
                                'border-emerald-200 bg-emerald-50 text-emerald-700' => ! $order->isCashOnStore(),
                            ])>
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                {{ $order->adminPaymentStatusLabel() }}
                            </span>
                        @endcan
                    @endif
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
            {{-- Left Column (Main Details) --}}
            <div class="space-y-6 lg:col-span-8">

                @include('admin.orders.partials._fulfillment-highlight', ['order' => $order])

                {{-- Order Details Card --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Order Information') }}</h3>
                    </div>
                    <div class="p-6">
                        {{-- Order contact --}}
                        <div class="mb-8">
                            @if($order->user_id && $order->hasDistinctContactFromAccount())
                                @php $order->loadMissing('user'); @endphp
                                <div class="mb-4 rounded-lg border border-violet-100 bg-violet-50/60 px-4 py-3 text-sm text-violet-900">
                                    {{ __('Placed on account of :name (:email). Contact details below are for the order recipient.', [
                                        'name' => $order->user->name,
                                        'email' => $order->user->email ?? __('no email'),
                                    ]) }}
                                </div>
                            @endif
                            <h4 class="mb-4 text-xs font-bold uppercase tracking-wider text-gray-400">{{ __('Contact for this order') }}</h4>
                            @if($order->isInStoreOrder())
                                <div class="mb-4 rounded-lg border border-violet-100 bg-violet-50/60 px-4 py-3 text-sm text-violet-900">
                                    <span class="font-semibold">{{ __('Order source') }}:</span>
                                    {{ __('In-store visit') }}
                                    @if($order->placedBy)
                                        · {{ __('placed by :name', ['name' => $order->placedBy->name]) }}
                                    @endif
                                </div>
                            @endif
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Name') }}</p>
                                    <p class="font-semibold text-gray-900">{{ $order->guest_name }}</p>
                                </div>
                                <div>
                                    <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Phone') }}</p>
                                    <p class="font-semibold text-gray-900"><a href="tel:{{ preg_replace('/\s+/', '', $order->guest_phone) }}" class="hover:text-indigo-600">{{ $order->guest_phone }}</a></p>
                                </div>
                                <div>
                                    <p class="mb-1 text-sm font-medium text-gray-500">{{ __('Email') }}</p>
                                    <p class="break-all font-semibold text-gray-900">
                                        @if($order->guest_email)
                                            <a href="mailto:{{ $order->guest_email }}" class="hover:text-indigo-600">{{ $order->guest_email }}</a>
                                        @else
                                            —
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        @if($order->user_id)
                            @include('admin.orders.partials._linked-account', ['order' => $order])
                        @endif

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

                        @include('admin.orders.partials._product-image-preview', ['order' => $order])
                    </div>
                </div>
            </div>

            {{-- Right Column (Sidebar) --}}
            <div class="space-y-6 lg:col-span-4">

                @include('admin.orders.partials._preparation-highlight', ['order' => $order, 'tz' => $tz])

                {{-- Delivery Highlight --}}
                <div class="flex items-center justify-between rounded-xl border border-indigo-100 bg-indigo-50 p-6 shadow-sm">
                    <div>
                        <p class="mb-1 text-sm font-bold uppercase tracking-wider text-indigo-600">{{ __('Scheduled Delivery') }}</p>
                        <h2 class="text-2xl font-bold text-indigo-900">{{ $deliveryAt?->format('F d, Y') }}</h2>
                        <p class="mt-0.5 text-lg font-medium text-indigo-700">{{ $deliveryAt?->format('h:i A') }}</p>
                    </div>
                    @if($deliveryAt)
                        <div class="text-right">
                            @if(in_array($order->order_status, ['completed', 'cancelled', 'delivered']))
                                <p class="mb-1 text-sm font-bold uppercase tracking-wider text-gray-500">{{ __('Status') }}</p>
                                <p class="text-xl font-bold text-gray-700">
                                    @if($order->order_status === 'delivered')
                                        {{ __('Delivered') }}
                                    @elseif($order->order_status === 'completed')
                                        {{ __('Completed') }}
                                    @else
                                        {{ __('Cancelled') }}
                                    @endif
                                </p>
                            @else
                                <p class="mb-1 text-sm font-bold uppercase tracking-wider {{ $deliveryAt->isPast() ? 'text-red-500' : 'text-indigo-600' }}">
                                    {{ $deliveryAt->isPast() ? __('Overdue by') : __('Time Left') }}
                                </p>
                                <p class="text-xl font-bold {{ $deliveryAt->isPast() ? 'text-red-600' : 'text-indigo-900' }}">
                                    {{ $deliveryAt->diffForHumans(null, true) }}
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
                
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
                                    <p class="font-bold text-gray-900">
                                        @if($order->product)
                                            <a href="{{ route('admin.products.edit', $order->product) }}" class="hover:text-indigo-600 hover:underline">{{ $order->displayProductName() }}</a>
                                        @else
                                            <span>{{ $order->displayProductName() }}</span>
                                        @endif
                                    </p>
                                    @include('order.partials._order-options', [
                                        'order' => $order,
                                        'weightClass' => 'mt-1 text-sm font-semibold text-indigo-700',
                                        'flavorClass' => 'mt-1 text-sm font-semibold text-rose-700',
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
                            <div class="flex items-center">
                                <div class="w-40 shrink-0 text-gray-500">{{ __('Unit Price') }}</div>
                                <div class="font-medium text-gray-900">₹ {{ number_format($order->displayUnitPrice(), 2) }}</div>
                            </div>
                        </div>

                        @if($order->hasDiscount())
                            <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4 text-sm">
                                <span class="text-gray-500">{{ __('Subtotal') }}</span>
                                <span class="font-medium text-gray-900">₹ {{ number_format($order->displaySubtotal(), 2) }}</span>
                            </div>
                            <div class="mt-2 flex items-center justify-between text-sm text-green-700">
                                <span>{{ __('Discount') }}@if($order->coupon_label) ({{ $order->coupon_label }})@endif</span>
                                <span>−₹ {{ number_format((float) $order->discount_amount, 2) }}</span>
                            </div>
                        @endif

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-4">
                            <span class="font-bold text-gray-900">{{ __('Total') }}</span>
                            <span class="text-xl font-bold text-indigo-600">₹ {{ number_format($order->amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Payment Details Card (Admin Only) --}}
                <div class="rounded-xl border border-gray-200 bg-white shadow-sm">
                    <div class="border-b border-gray-100 bg-gray-50/80 px-6 py-4">
                        <h3 class="font-semibold text-gray-900">{{ __('Payment Details') }}</h3>
                    </div>
                    <div class="p-6">
                        @if($order->isCashOnStore())
                            <div class="space-y-4">
                                <div class="rounded-lg border border-violet-200 bg-violet-50 p-4">
                                    <p class="text-sm font-semibold text-violet-900">{{ __('Cash collected in store') }}</p>
                                    <p class="mt-1 text-sm text-violet-800">{{ __('This order was placed during an in-store visit. Payment is recorded as cash on store — no UPI reference or payment proof is required.') }}</p>
                                </div>
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Payment method') }}</p>
                                        <p class="font-semibold text-gray-900">{{ $order->paymentMethodLabel() }}</p>
                                    </div>
                                    <div>
                                        <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Amount') }}</p>
                                        <p class="font-semibold text-gray-900">₹ {{ number_format($order->amount, 2) }}</p>
                                    </div>
                                </div>
                                @if($order->placedBy)
                                    <div>
                                        <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Placed by staff') }}</p>
                                        <p class="font-semibold text-gray-900">{{ $order->placedBy->name }}</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($order->payment_reference || $order->payment_amount !== null || $order->payment_made_at)
                            <div class="space-y-4">
                                @if($order->payment_reference)
                                    <div>
                                        <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Reference') }}</p>
                                        <p class="font-semibold text-gray-900">{{ $order->payment_reference }}</p>
                                    </div>
                                @endif
                                
                                <div class="grid grid-cols-2 gap-4">
                                    @if($order->payment_amount !== null)
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Amount Paid') }}</p>
                                            <p class="font-semibold text-gray-900">₹ {{ number_format($order->payment_amount, 2) }}</p>
                                        </div>
                                    @endif
                                    @if($order->payment_made_at)
                                        <div>
                                            <p class="mb-1 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Date') }}</p>
                                            <p class="font-semibold text-gray-900">{{ $order->payment_made_at->setTimezone($tz)->format('M d, Y') }}</p>
                                        </div>
                                    @endif
                                </div>
                                
                                @php $proof = $order->getFirstMedia('payment_proof'); @endphp
                                @if($proof)
                                    <div class="border-t border-gray-100 pt-4">
                                        <p class="mb-2 text-xs font-medium uppercase tracking-wider text-gray-500">{{ __('Payment Proof') }}</p>
                                        <a href="{{ $proof->getUrl() }}" target="_blank" class="group block overflow-hidden rounded-lg border border-gray-200">
                                            {{-- Fallback logic for thumbnail. If image conversion fails, just shows the original image --}}
                                            <img src="{{ $proof->getUrl() }}" alt="Proof" class="h-32 w-full object-contain transition duration-300 group-hover:scale-105" onerror="this.onerror=null; this.src='{{ $proof->getUrl() }}';" />
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-4 text-center">
                                <svg class="mx-auto mb-3 h-8 w-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <p class="text-sm text-gray-500">{{ __('No payment details submitted.') }}</p>
                            </div>
                        @endif
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