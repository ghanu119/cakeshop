@php
    $customerContext = app(\App\Services\CustomerContext::class);
    $customerUser = auth('customer')->user();
    $isImpersonating = $customerContext->isImpersonating();
    $effectiveCustomer = $customerContext->effectiveCustomer();
@endphp
@if($isImpersonating && $effectiveCustomer)
    <span class="hidden rounded-lg bg-indigo-50 px-3 py-2 text-sm font-medium text-indigo-800 lg:inline">{{ __('Ordering as :name', ['name' => $effectiveCustomer->name]) }}</span>
    <a href="{{ route('admin.dashboard') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-indigo-700 hover:text-indigo-900">{{ __('Admin') }}</a>
@elseif($customerUser)
    <a href="{{ route('account.dashboard') }}" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-600 hover:text-amber-600">{{ __('Account') }}</a>
    <form method="post" action="{{ route('account.logout') }}" class="inline">
        @csrf
        <button type="submit" class="nav-link rounded-lg px-3 py-2 text-base font-medium text-stone-500 hover:text-stone-800">{{ __('Sign out') }}</button>
    </form>
@else
    <x-open-auth-modal-button class="nav-link rounded-lg bg-stone-900 px-4 py-2 text-sm font-medium text-white hover:bg-stone-800" />
@endif
