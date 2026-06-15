@php
    $accountCustomer = auth()->user()?->isCustomer() ? auth()->user() : null;
    $linkClass = 'flex w-full sm:w-auto items-center justify-center gap-2 rounded-full border border-stone-200 bg-white px-5 py-2.5 font-semibold text-stone-500 shadow-sm transition-colors hover:text-stone-900';
    $wrapperClass = $wrapperClass ?? 'mt-8';
@endphp

<div class="{{ $wrapperClass }} flex flex-col items-stretch justify-center gap-4 text-center sm:flex-row sm:items-center sm:gap-6">
    @if($accountCustomer)
        <a href="{{ route('account.orders.index') }}" class="{{ $linkClass }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            {{ __('My orders') }}
        </a>
        <a href="{{ route('account.dashboard') }}" class="{{ $linkClass }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            {{ __('Back to account') }}
        </a>
    @else
        <a href="{{ route('order.history') }}" class="{{ $linkClass }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            {{ __('Look up your order') }}
        </a>
        <a href="{{ route('home') }}" class="{{ $linkClass }}">
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
            {{ __('Back to home') }}
        </a>
    @endif
</div>
