@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="mb-8">
        <h1 class="text-3xl font-bold tracking-tight text-stone-900">{{ __('Hello, :name', ['name' => $customer->name]) }}</h1>
        <p class="mt-2 text-stone-600">{{ __('Welcome back to your account.') }}</p>
    </header>

    <div class="mb-8 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('products.index') }}" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
            <p class="font-semibold text-stone-900">{{ __('Order a cake') }}</p>
            <p class="mt-1 text-sm text-stone-500">{{ __('Browse our menu') }}</p>
        </a>
        <a href="{{ route('account.orders.index') }}" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
            <p class="font-semibold text-stone-900">{{ __('My orders') }}</p>
            <p class="mt-1 text-sm text-stone-500">{{ __('View order history') }}</p>
        </a>
        <a href="{{ route('account.profile.edit') }}" class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm transition hover:border-amber-300">
            <p class="font-semibold text-stone-900">{{ __('Profile') }}</p>
            <p class="mt-1 text-sm text-stone-500">{{ __('Update your details') }}</p>
        </a>
    </div>

    <h2 class="mb-4 text-xl font-semibold text-stone-900">{{ __('Recent orders') }}</h2>
    @forelse($recentOrders as $order)
        @include('account.partials._order-card', ['order' => $order])
    @empty
        <x-card>
            <p class="text-stone-600">{{ __('You have not placed any orders yet.') }}</p>
            <a href="{{ route('products.index') }}" class="mt-4 inline-flex text-amber-700 hover:underline">{{ __('Browse cakes') }}</a>
        </x-card>
    @endforelse
</div>
@endsection
