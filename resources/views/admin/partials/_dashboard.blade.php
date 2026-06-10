@php
    $stats = $adminDashboard['stats'];
    $todayDeliveries = $adminDashboard['todayDeliveries'];
    $todayDeliveriesTotal = $adminDashboard['todayDeliveriesTotal'];
    $inKitchenOrders = $adminDashboard['inKitchenOrders'];
    $inKitchenTotal = $adminDashboard['inKitchenTotal'];
    $inKitchenOverdueCount = $adminDashboard['inKitchenOverdueCount'];
    $upcomingOrders = $adminDashboard['upcomingOrders'];
    $upcomingTotal = $adminDashboard['upcomingTotal'];
    $paymentReviewOrders = $adminDashboard['paymentReviewOrders'];
    $paymentReviewTotal = $adminDashboard['paymentReviewTotal'];
    $recentOrders = $adminDashboard['recentOrders'];
    $hasEnquiries = isset($recentEnquiries) && $recentEnquiries !== null && $recentEnquiries->isNotEmpty();
@endphp

<div class="admin-dashboard space-y-6">
    {{-- KPI row (4 cards) --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('admin.orders.index', ['delivery_today' => 1]) }}" data-highlight-target="deliveries_today" class="group rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.03] transition hover:border-orange-200 hover:shadow-md {{ in_array('deliveries_today', ($unreadHighlightTargets ?? collect())->toArray(), true) ? 'notification-highlight' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                </div>
                @if($stats['awaitingVerification'] > 0)
                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[10px] font-bold uppercase text-amber-700">{{ $stats['awaitingVerification'] }} {{ __('verify') }}</span>
                @endif
            </div>
            <p class="mt-4 text-3xl font-black tracking-tight text-gray-900">{{ $stats['deliveriesToday'] }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500">{{ __('Deliveries today') }}</p>
        </a>

        <a href="{{ route('admin.kitchen.orders.index') }}" class="group rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.03] transition hover:border-orange-200 hover:shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wide {{ $inKitchenOverdueCount > 0 ? 'text-red-500' : 'text-emerald-600' }}">
                    {{ $inKitchenOverdueCount > 0 ? __('Needs attention') : __('Active now') }}
                </span>
            </div>
            <p class="mt-4 text-3xl font-black tracking-tight text-gray-900">{{ $stats['inKitchen'] }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500">{{ __('Orders in kitchen') }}</p>
        </a>

        <div class="rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.03]">
            <div class="flex items-start justify-between gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-100 text-orange-600">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('Live tracking') }}</span>
            </div>
            <p class="mt-4 text-3xl font-black tracking-tight text-gray-900">₹{{ number_format($stats['revenueToday'], 0) }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500">{{ __('Revenue today') }}</p>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="group rounded-2xl border border-gray-200/80 bg-white p-5 shadow-sm ring-1 ring-black/[0.03] transition hover:border-orange-200 hover:shadow-md">
            <div class="flex items-start justify-between gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-slate-100 text-slate-600">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
                <span class="text-[10px] font-bold uppercase tracking-wide text-gray-400">{{ __('Weekly goal') }}</span>
            </div>
            <p class="mt-4 text-3xl font-black tracking-tight text-gray-900">{{ $stats['ordersThisWeek'] }}</p>
            <p class="mt-1 text-sm font-medium text-gray-500">{{ __('Orders this week') }}</p>
        </a>
    </div>

    {{-- Quick links --}}
    <div class="flex flex-wrap items-center gap-2">
        @if($productsCount !== null)
            <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-orange-200 hover:text-orange-700">
                {{ __('Products') }}
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold tabular-nums text-gray-900">{{ $productsCount }}</span>
            </a>
        @endif
        @if($categoriesCount !== null)
            <a href="{{ route('admin.categories.index') }}" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-600 shadow-sm transition hover:border-orange-200 hover:text-orange-700">
                {{ __('Categories') }}
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-bold tabular-nums text-gray-900">{{ $categoriesCount }}</span>
            </a>
        @endif
        <a href="{{ route('admin.kitchen.orders.index') }}" class="inline-flex items-center gap-1.5 rounded-full border-2 border-orange-500 bg-orange-100 px-4 py-2 text-sm font-bold text-orange-900 shadow-sm transition hover:border-orange-600 hover:bg-orange-200">
            {{ __('Kitchen queue') }}
            @if($stats['inKitchen'] > 0)
                <span class="rounded-full border border-orange-600 bg-white px-2 py-0.5 text-xs font-black tabular-nums text-orange-700">{{ $stats['inKitchen'] }}</span>
            @endif
        </a>
    </div>

    {{-- Today's deliveries — full width --}}
    <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/[0.03]">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-4 py-4 sm:px-6">
            <div class="flex flex-wrap items-center gap-3">
                <h2 class="text-base font-black uppercase tracking-tight text-gray-900">{{ __("Today's Deliveries") }}</h2>
                @if($todayDeliveriesTotal > 0)
                    <span class="rounded-md bg-orange-500 px-2 py-0.5 text-[10px] font-black uppercase tracking-wider text-white">
                        {{ $todayDeliveriesTotal }} {{ __('orders') }}
                    </span>
                @endif
            </div>
            <a href="{{ route('admin.orders.index', ['delivery_today' => 1]) }}" class="text-[10px] font-black uppercase tracking-widest text-orange-600 hover:text-orange-700">
                {{ __('View all schedule') }} →
            </a>
        </div>

        @if($todayDeliveries->isNotEmpty())
            <table class="w-full table-fixed divide-y divide-gray-100">
                <colgroup>
                    <col class="w-[11rem]" />
                    <col class="w-auto" />
                    <col class="w-[10rem]" />
                    <col class="w-[7.5rem]" />
                    <col class="w-[8.5rem]" />
                </colgroup>
                <thead class="bg-gray-50/80">
                    <tr>
                        <th scope="col" class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6">{{ __('Order ID') }}</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-4">{{ __('Product') }}</th>
                        <th scope="col" class="hidden px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 md:table-cell md:px-4">{{ __('Customer') }}</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-4">{{ __('Status') }}</th>
                        <th scope="col" class="px-4 py-2.5 text-left text-[10px] font-black uppercase tracking-widest text-gray-400 sm:px-6">{{ __('Payment') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @foreach($todayDeliveries as $order)
                        @include('admin.orders.partials._dashboard-delivery-table-row', ['order' => $order])
                    @endforeach
                </tbody>
            </table>
            @if($todayDeliveriesTotal > $todayDeliveries->count())
                <div class="border-t border-gray-100 bg-orange-50/30 px-4 py-3 text-center sm:px-6">
                    <a href="{{ route('admin.orders.index', ['delivery_today' => 1]) }}" class="text-xs font-bold uppercase tracking-wide text-orange-600 hover:text-orange-700">
                        {{ __('+:count more deliveries', ['count' => $todayDeliveriesTotal - $todayDeliveries->count()]) }} →
                    </a>
                </div>
            @endif
        @else
            <div class="px-6 py-16 text-center">
                <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center rounded-2xl bg-orange-50 text-orange-400">
                    <svg class="h-7 w-7 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <p class="font-semibold text-gray-900">{{ __('No deliveries today') }}</p>
                <p class="mt-1 text-sm text-gray-500">{{ __('Your schedule is clear for now.') }}</p>
            </div>
        @endif
    </section>

    {{-- Bottom row: recent activity (left) + operations (right) --}}
    <div class="grid items-start gap-6 lg:grid-cols-2">
        <div class="min-w-0 space-y-6">
            @if($recentOrders->isNotEmpty())
                <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/[0.03]">
                    <div class="border-b border-gray-100 px-4 py-4 sm:px-6">
                        <h2 class="text-base font-black uppercase tracking-tight text-gray-900">{{ __('Recently Placed') }}</h2>
                    </div>
                    <div>
                        @foreach($recentOrders as $order)
                            @include('admin.orders.partials._dashboard-recent-activity-row', ['order' => $order])
                        @endforeach
                    </div>
                    <div class="border-t border-gray-100 px-4 py-3 text-center sm:px-6">
                        <a href="{{ route('admin.orders.index') }}" class="text-xs font-bold uppercase tracking-wide text-orange-600 hover:text-orange-700">{{ __('View all orders') }} →</a>
                    </div>
                </section>
            @endif

            @if($hasEnquiries)
                <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm ring-1 ring-black/[0.03] sm:p-6">
                    <div class="mb-3 flex items-center justify-between">
                        <h2 class="text-sm font-black uppercase tracking-tight text-gray-900">{{ __('Contact Enquiries') }}</h2>
                        <a href="{{ route('admin.contact-enquiries.index') }}" class="text-xs font-bold text-orange-600">{{ __('View all') }}</a>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($recentEnquiries as $enquiry)
                            <li class="flex items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="font-semibold text-gray-900">{{ $enquiry->name }}</p>
                                    <p class="truncate text-sm text-gray-500">{{ $enquiry->subject }}</p>
                                </div>
                                <span class="shrink-0 text-xs text-gray-400">{{ $enquiry->created_at->format('d M') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Operations column — fills right half --}}
        <aside class="min-w-0 space-y-5 lg:sticky lg:top-6">
            <div class="grid gap-5 xl:grid-cols-2">
            {{-- In Kitchen --}}
            <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5">
                    <h2 class="text-sm font-black uppercase tracking-tight text-gray-900">{{ __('In Kitchen') }}</h2>
                    <a href="{{ route('admin.kitchen.orders.index') }}" class="text-[10px] font-black uppercase tracking-widest text-orange-600 hover:text-orange-700">{{ __('View queue') }} →</a>
                </div>
                <div class="divide-y divide-gray-100">
                    @if($inKitchenOrders->isNotEmpty())
                        @foreach($inKitchenOrders as $order)
                            @include('admin.orders.partials._kitchen-compact-row', ['order' => $order])
                        @endforeach
                        @if($inKitchenTotal > $inKitchenOrders->count())
                            <div class="border-t border-gray-100 px-4 py-3 text-center">
                                <a href="{{ route('admin.kitchen.orders.index') }}" class="text-xs font-bold text-orange-600">
                                    {{ __('+:count more', ['count' => $inKitchenTotal - $inKitchenOrders->count()]) }} →
                                </a>
                            </div>
                        @endif
                    @else
                        <p class="px-4 py-6 text-center text-sm text-gray-500">{{ __('No orders in production.') }}</p>
                    @endif
                </div>
            </section>

            {{-- Upcoming --}}
            <section class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm ring-1 ring-black/[0.03]">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3.5 sm:px-5">
                    <h2 class="text-sm font-black uppercase tracking-tight text-gray-900">{{ __('Upcoming Delivery') }}</h2>
                    @if($upcomingTotal > 0)
                        <span class="rounded-full bg-gray-100 px-2.5 py-0.5 text-[10px] font-black uppercase text-gray-600">{{ $upcomingTotal }} {{ __('scheduled') }}</span>
                    @endif
                </div>
                <div class="space-y-3 p-4">
                    @forelse($upcomingOrders as $order)
                        @include('admin.orders.partials._dashboard-upcoming-card', ['order' => $order])
                    @empty
                        <p class="py-6 text-center text-sm text-gray-500">{{ __('No upcoming deliveries.') }}</p>
                    @endforelse
                    @if($upcomingTotal > $upcomingOrders->count())
                        <a href="{{ route('admin.orders.index') }}" class="block text-center text-xs font-bold text-orange-600">
                            {{ __('+:count more', ['count' => $upcomingTotal - $upcomingOrders->count()]) }} →
                        </a>
                    @endif
                </div>
            </section>
            </div>

            {{-- Payments — full width of right column --}}
            @if($paymentReviewTotal > 0)
                <section data-highlight-target="payment_review" class="overflow-hidden rounded-2xl border-2 border-amber-200 bg-white shadow-sm {{ in_array('payment_review', ($unreadHighlightTargets ?? collect())->toArray(), true) ? 'notification-highlight' : '' }}">
                    <div class="flex items-center justify-between border-b border-amber-100 bg-amber-50 px-4 py-3">
                        <h2 class="text-sm font-black uppercase text-amber-950">{{ __('Review Payments') }}</h2>
                        <span class="rounded-full bg-amber-500 px-2 py-0.5 text-[10px] font-black text-white">{{ $paymentReviewTotal }}</span>
                    </div>
                    <div class="space-y-2 p-3">
                        @foreach($paymentReviewOrders as $order)
                            @include('admin.orders.partials._payment-review-row', ['order' => $order])
                        @endforeach
                    </div>
                    @if($paymentReviewTotal > $paymentReviewOrders->count())
                        <div class="border-t border-amber-100 px-4 py-2 text-center">
                            <a href="{{ route('admin.orders.index', ['awaiting_payment_verification' => 1]) }}" class="text-xs font-bold text-amber-800">
                                {{ __('+:count more to review', ['count' => $paymentReviewTotal - $paymentReviewOrders->count()]) }} →
                            </a>
                        </div>
                    @endif
                </section>
            @else
                <div class="flex items-center gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3.5 shadow-sm">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-emerald-900">{{ __('Payments up to date') }}</p>
                        <p class="text-xs text-emerald-700">{{ __('No payments awaiting verification.') }}</p>
                    </div>
                </div>
            @endif
        </aside>
    </div>

    {{-- FAB --}}
    <a
        href="{{ route('admin.orders.index') }}"
        class="fixed bottom-8 right-8 z-40 flex h-14 w-14 items-center justify-center rounded-full bg-orange-500 text-white shadow-lg shadow-orange-500/30 transition hover:scale-105 hover:bg-orange-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500 focus-visible:ring-offset-2"
        title="{{ __('All orders') }}"
    >
        <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
    </a>
</div>
