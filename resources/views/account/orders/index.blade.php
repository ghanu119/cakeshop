@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
    <header class="mb-6">
        <a href="{{ route('account.dashboard') }}" class="text-sm text-stone-600 hover:underline">{{ __('← Account') }}</a>
        <h1 class="mt-2 text-3xl font-bold tracking-tight text-stone-900">{{ __('My orders') }}</h1>
    </header>

    @forelse($orders as $order)
        @include('account.partials._order-card', ['order' => $order, 'showAmount' => true])
    @empty
        <x-card>
            <p class="text-stone-600">{{ __('No orders yet.') }}</p>
        </x-card>
    @endforelse

    @if($orders->hasPages())
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</div>
@endsection
