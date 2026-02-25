@extends('layouts.admin')

@section('title', __('Dashboard'))

@section('content')
    <header class="mb-8">
        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $header }}</h1>
        <p class="mt-1 text-sm text-gray-500">{{ __('Overview of your store') }}</p>
    </header>

    @can('orders.view')
    <div class="mb-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
        @if($ordersCount !== null)
        <a href="{{ route('admin.orders.index') }}" class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:border-indigo-200 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Total orders') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $ordersCount }}</p>
                    <span class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 opacity-0 transition group-hover:opacity-100">{{ __('View orders') }} →</span>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                </div>
            </div>
        </a>
        @endif
        @if($productsCount !== null)
        <a href="{{ route('admin.products.index') }}" class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:border-indigo-200 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Products') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $productsCount }}</p>
                    <span class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 opacity-0 transition group-hover:opacity-100">{{ __('View products') }} →</span>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 4-8-4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" /></svg>
                </div>
            </div>
        </a>
        @endif
        @if($categoriesCount !== null)
        <a href="{{ route('admin.categories.index') }}" class="group rounded-xl border border-gray-200 bg-white p-6 shadow-sm transition duration-200 hover:border-indigo-200 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">{{ __('Categories') }}</p>
                    <p class="mt-2 text-3xl font-bold text-gray-900">{{ $categoriesCount }}</p>
                    <span class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 opacity-0 transition group-hover:opacity-100">{{ __('View categories') }} →</span>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
            </div>
        </a>
        @endif
        <a href="{{ route('kitchen.orders.index') }}" class="group rounded-xl border-2 border-indigo-200 bg-indigo-50/50 p-6 shadow-sm transition duration-200 hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-indigo-700">{{ __("Today's orders") }}</p>
                    <p class="mt-2 text-lg font-semibold text-indigo-900">{{ __('Kitchen view') }}</p>
                    <span class="mt-2 inline-flex items-center text-sm font-medium text-indigo-700">{{ __("Open kitchen") }} →</span>
                </div>
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-indigo-100 text-indigo-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
        </a>
    </div>
    @endcan

    @can('contact_enquiries.view')
    @if($recentEnquiries !== null && $recentEnquiries->isNotEmpty())
    <x-card class="overflow-hidden">
        <div class="flex items-center justify-between border-b border-gray-100 pb-4">
            <h2 class="text-lg font-semibold text-gray-900">{{ __('Recent contact enquiries') }}</h2>
            <a href="{{ route('admin.contact-enquiries.index') }}" class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-medium text-indigo-600 transition hover:bg-indigo-50">{{ __('View all') }} →</a>
        </div>
        <ul class="divide-y divide-gray-100">
            @foreach($recentEnquiries as $enquiry)
                <li class="flex items-center justify-between py-3 first:pt-0">
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-900">{{ $enquiry->name }}</p>
                        <p class="truncate text-sm text-gray-500">{{ $enquiry->subject }}</p>
                    </div>
                    <span class="ml-4 shrink-0 text-sm text-gray-400">{{ $enquiry->created_at->format('d M Y') }}</span>
                </li>
            @endforeach
        </ul>
    </x-card>
    @endif
    @endcan

    @can('orders.view')
    <div class="mt-8">
        <a href="{{ route('kitchen.orders.index') }}" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition duration-200 hover:bg-indigo-700">{{ __("Today's orders") }}</a>
    </div>
    @endcan

    @if(!auth()->user()->can('orders.view') && !auth()->user()->can('contact_enquiries.view'))
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <p class="text-gray-900">{{ __("You're logged in!") }}</p>
        <a href="{{ route('kitchen.orders.index') }}" class="mt-2 inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-700">{{ __("Today's orders") }}</a>
    </div>
    @endif
@endsection
