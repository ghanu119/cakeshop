<div class="rounded-2xl border border-amber-200 bg-amber-50/90 px-5 py-4 text-sm text-stone-800" role="alert">
    <p data-payment-error-message>{{ $message ?? '' }}</p>
    @if($retryable ?? true)
        <button
            type="button"
            data-payment-error-retry
            class="mt-3 inline-flex items-center rounded-full border border-amber-300 bg-white px-4 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-50"
        >
            {{ __('payments.try_again') }}
        </button>
    @endif
</div>
