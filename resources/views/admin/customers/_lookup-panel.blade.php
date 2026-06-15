@php
    $match = $lookup['match'] ?? null;
    $conflict = (bool) ($lookup['conflict'] ?? false);
    $visible = $conflict || $match !== null;
    $emailLabel = filled($match['email'] ?? null) ? $match['email'] : __('No email');
@endphp

<div
    id="customer-lookup-panel"
    @class([
        'rounded-lg border border-amber-200 bg-amber-50 p-4',
        'hidden' => ! $visible,
    ])
    @if($visible) data-server-rendered="true" @endif
>
    @if($conflict)
        <p class="font-semibold text-amber-900">{{ $lookup['message'] }}</p>
        @if(($lookup['email_match'] ?? null) && ($lookup['phone_match'] ?? null))
            <p class="mt-2 text-sm text-amber-800">
                {{ __('Email') }}: {{ $lookup['email_match']['name'] }} · {{ $lookup['email_match']['email'] ?? __('No email') }} · {{ $lookup['email_match']['phone'] }}
            </p>
            <p class="mt-1 text-sm text-amber-800">
                {{ __('Phone') }}: {{ $lookup['phone_match']['name'] }} · {{ $lookup['phone_match']['email'] ?? __('No email') }} · {{ $lookup['phone_match']['phone'] }}
            </p>
        @endif
    @elseif($match)
        <p class="font-semibold text-amber-900">{{ __('Matching customer found') }}</p>
        <p class="mt-1 text-sm text-amber-800">{{ $match['name'] }} · {{ $emailLabel }} · {{ $match['phone'] }}</p>
        <p class="mt-1 text-sm text-amber-800">{{ $match['orders_count'] }} {{ __('orders') }} · {{ $match['created_at'] }}</p>
        <div class="mt-3 flex flex-wrap gap-3">
            <a href="{{ $match['view_url'] }}" class="text-sm font-medium text-indigo-700 hover:underline">{{ __('View profile') }}</a>
            <form method="post" action="{{ $match['impersonate_url'] }}" class="inline">
                @csrf
                <button type="submit" class="text-sm font-semibold text-indigo-700 hover:underline">{{ __('Shop as customer') }}</button>
            </form>
        </div>
    @endif
</div>
