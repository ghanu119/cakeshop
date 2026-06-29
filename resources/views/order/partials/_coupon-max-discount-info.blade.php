@props([
    'maxCap' => null,
    'symbol' => '₹',
])

@if($maxCap !== null && (float) $maxCap > 0)
    @php
        $message = __('Maximum discount of :amount applies to this offer.', [
            'amount' => $symbol . number_format((float) $maxCap, 2),
        ]);
    @endphp
    <span class="coupon-max-discount-info relative inline-flex shrink-0 align-middle" data-max-discount-info>
        <button
            type="button"
            class="coupon-max-discount-info__trigger inline-flex h-4 w-4 items-center justify-center rounded-full text-stone-400 hover:text-stone-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50"
            data-max-discount-trigger
            aria-expanded="false"
            aria-label="{{ __('Maximum discount information') }}"
        >
            <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
            </svg>
        </button>
        <span class="coupon-max-discount-info__popover" data-max-discount-popover role="tooltip">{{ $message }}</span>
    </span>
@endif
