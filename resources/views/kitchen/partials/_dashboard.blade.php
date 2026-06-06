@php
    $tz = settings('timezone') ?? 'Asia/Kolkata';
    $todayLabel = now($tz)->format('l, j F Y');
    $todayCount = $todayOrders?->count() ?? 0;
    $upcomingCount = $upcomingTotal ?? 0;
    $overdueCount = $todayOrders
        ? $todayOrders->filter(function ($order) use ($tz) {
            $prep = $order->preparation_at?->setTimezone($tz);

            return $prep && $prep->isPast() && $order->order_status === 'processing';
        })->count()
        : 0;
@endphp

{{-- Summary strip --}}
<div class="mb-8 grid gap-4 sm:grid-cols-3">
    <div class="flex items-center gap-4 rounded-2xl border border-indigo-100 bg-gradient-to-br from-indigo-50 to-white p-5 shadow-sm">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-200">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-indigo-600">{{ __("Today's queue") }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $todayCount }}</p>
        </div>
    </div>

    <div class="flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-gray-100 text-gray-600">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-sm font-medium text-gray-500">{{ __('Upcoming') }}</p>
            <p class="text-3xl font-bold text-gray-900">{{ $upcomingCount }}</p>
        </div>
    </div>

    <div class="flex items-center gap-4 rounded-2xl border p-5 shadow-sm {{ $overdueCount > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-100 bg-emerald-50/50' }}">
        <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl {{ $overdueCount > 0 ? 'bg-red-500 text-white' : 'bg-emerald-100 text-emerald-600' }}">
            @if($overdueCount > 0)
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            @else
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            @endif
        </div>
        <div>
            <p class="text-sm font-medium {{ $overdueCount > 0 ? 'text-red-600' : 'text-emerald-700' }}">
                {{ $overdueCount > 0 ? __('Needs attention') : __('On track') }}
            </p>
            <p class="text-3xl font-bold {{ $overdueCount > 0 ? 'text-red-800' : 'text-gray-900' }}">
                {{ $overdueCount > 0 ? $overdueCount : '✓' }}
            </p>
        </div>
    </div>
</div>

<div class="grid gap-8 lg:grid-cols-12">
    {{-- Today's Orders --}}
    <section class="lg:col-span-8">
        <x-card class="overflow-hidden !p-0">
            <div class="flex flex-col gap-3 border-b border-gray-100 bg-gray-50/80 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __("Today's Orders") }}</h2>
                        <p class="text-xs text-gray-500">{{ __('Tap a card to open and mark complete') }}</p>
                    </div>
                    @if($todayCount > 0)
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-bold text-indigo-700">{{ $todayCount }}</span>
                    @endif
                </div>
                <a
                    href="{{ route('admin.kitchen.orders.index') }}"
                    class="inline-flex items-center justify-center rounded-lg bg-white px-4 py-2 text-sm font-semibold text-indigo-600 shadow-sm ring-1 ring-indigo-100 transition hover:bg-indigo-50"
                >
                    {{ __('Full list') }} →
                </a>
            </div>

            <div class="p-5 sm:p-6">
                @if($todayOrders && $todayOrders->isNotEmpty())
                    <div class="grid gap-5 sm:grid-cols-2">
                        @foreach($todayOrders as $order)
                            @include('kitchen.orders.partials._today-order-card', ['order' => $order])
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center rounded-2xl border-2 border-dashed border-gray-200 bg-gray-50/50 px-6 py-14 text-center">
                        <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 15.546c-.523 0-1.046.151-1.5.454a2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.704 2.704 0 00-3 0 2.704 2.704 0 01-3 0 2.701 2.701 0 00-1.5-.454M9 6v2m3-2v2m3-2v2M9 3h.01M12 3h.01M15 3h.01M21 21v-7a2 2 0 00-2-2H5a2 2 0 00-2 2v7h18z"/></svg>
                        </div>
                        <p class="max-w-sm font-medium text-gray-900">{{ __('Nothing in production yet') }}</p>
                        <p class="mt-2 max-w-sm text-sm text-gray-500">{{ __('Orders appear here after payment is verified and status is set to Processing with a prep time.') }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    </section>

    {{-- Upcoming Orders --}}
    <section class="lg:col-span-4">
        <x-card class="flex h-full flex-col overflow-hidden !p-0">
            <div class="flex items-center justify-between gap-3 border-b border-gray-100 bg-gray-50/80 px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-200 text-gray-600">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">{{ __('Upcoming') }}</h2>
                        <p class="text-xs text-gray-500">{{ __('Read-only preview') }}</p>
                    </div>
                    @if($upcomingCount > 0)
                        <span class="rounded-full bg-gray-200 px-2.5 py-0.5 text-xs font-bold text-gray-700">{{ $upcomingCount }}</span>
                    @endif
                </div>
            </div>

            <div class="flex flex-1 flex-col p-4">
                @if($upcomingOrders && $upcomingOrders->isNotEmpty())
                    <div class="space-y-2.5">
                        @foreach($upcomingOrders as $order)
                            @include('kitchen.orders.partials._upcoming-order-preview', ['order' => $order])
                        @endforeach
                    </div>
                    @if($upcomingCount > $upcomingOrders->count())
                        <a
                            href="{{ route('admin.kitchen.orders.upcoming') }}"
                            class="mt-4 flex items-center justify-center rounded-xl border border-dashed border-indigo-200 bg-indigo-50/50 px-4 py-3 text-sm font-semibold text-indigo-700 transition hover:border-indigo-300 hover:bg-indigo-50"
                        >
                            {{ __('+:count more orders', ['count' => $upcomingCount - $upcomingOrders->count()]) }}
                        </a>
                    @elseif($upcomingCount > 0)
                        <a
                            href="{{ route('admin.kitchen.orders.upcoming') }}"
                            class="mt-4 text-center text-sm font-medium text-indigo-600 transition hover:text-indigo-800"
                        >
                            {{ __('View all upcoming') }} →
                        </a>
                    @endif
                @else
                    <div class="flex flex-1 flex-col items-center justify-center rounded-xl border border-dashed border-gray-200 bg-gray-50/50 px-4 py-10 text-center">
                        <svg class="mb-3 h-10 w-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p class="text-sm font-medium text-gray-700">{{ __('No upcoming orders') }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ __('Verified future orders will show here.') }}</p>
                    </div>
                @endif
            </div>
        </x-card>
    </section>
</div>
