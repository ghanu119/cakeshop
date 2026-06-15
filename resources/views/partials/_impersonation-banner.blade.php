@php
    $customerContext = app(\App\Services\CustomerContext::class);
    $effectiveCustomer = $customerContext->effectiveCustomer();
    $isImpersonating = $customerContext->isImpersonating();
    $stickyHeader = in_array(active_theme(), ['warm', 'better-buns'], true);
@endphp
@if($isImpersonating && $effectiveCustomer)
    <div @class([
        'border-b border-indigo-200 bg-indigo-600 px-4 py-3 text-center text-sm text-white shadow-md',
        'w-full' => $stickyHeader,
        'fixed top-20 left-0 right-0 z-40' => ! $stickyHeader,
    ])>
        <span>{{ __('Ordering for :name (on behalf of customer)', ['name' => $effectiveCustomer->name]) }}</span>
        <span class="mx-2">·</span>
        <a href="{{ route('admin.dashboard') }}" class="font-medium underline hover:no-underline">{{ __('Return to admin') }}</a>
        <span class="mx-2">·</span>
        <form method="post" action="{{ route('admin.impersonation.stop') }}" class="inline">
            @csrf
            <button type="submit" class="font-medium underline hover:no-underline">{{ __('Stop shopping as customer') }}</button>
        </form>
    </div>
@endif
