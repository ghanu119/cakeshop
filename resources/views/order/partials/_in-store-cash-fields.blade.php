@php
    $isImpersonating = $isImpersonating ?? false;
    $currencySymbol = $currencySymbol ?? '₹';
@endphp

@if($isImpersonating)
    <div
        id="order-confirm-in-store-cash-section"
        class="mt-6 rounded-xl border border-violet-200 bg-violet-50/80 p-4"
        data-in-store-cash-section
    >
        <p class="text-xs font-bold uppercase tracking-wider text-violet-700">{{ __('In-store payment') }}</p>
        <p class="mt-1 text-sm text-violet-900/90">{{ __('Record any cash received now. Leave at 0 if the customer will pay later.') }}</p>

        <label for="order-confirm-cash-received" class="mb-1 mt-4 block text-sm font-medium text-stone-700">
            {{ __('Cash received now') }}
        </label>
        <div class="flex overflow-hidden rounded-lg border border-stone-300 bg-white shadow-sm focus-within:border-violet-500 focus-within:ring-1 focus-within:ring-violet-500">
            <span class="flex shrink-0 items-center border-r border-stone-200 bg-stone-50 px-3 text-sm font-semibold text-stone-600">{{ $currencySymbol }}</span>
            <input
                type="text"
                id="order-confirm-cash-received"
                inputmode="decimal"
                autocomplete="off"
                value="{{ old('cash_received', '0') }}"
                class="block w-full min-w-0 border-0 bg-transparent py-2.5 pl-3 pr-3 text-base font-semibold text-stone-900 focus:outline-none focus:ring-0"
                data-in-store-cash-input
            />
        </div>

        <dl class="mt-4 space-y-2 rounded-lg border border-violet-100 bg-white/80 px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-3">
                <dt class="text-stone-600">{{ __('Order total') }}</dt>
                <dd class="font-bold text-stone-900" data-in-store-order-total>—</dd>
            </div>
            <div class="flex items-center justify-between gap-3">
                <dt class="text-stone-600">{{ __('Cash received') }}</dt>
                <dd class="font-semibold text-violet-800" data-in-store-cash-display>{{ $currencySymbol }}0.00</dd>
            </div>
            <div class="flex items-center justify-between gap-3 border-t border-violet-100 pt-2">
                <dt class="font-semibold text-stone-700">{{ __('Balance due later') }}</dt>
                <dd class="text-base font-bold text-amber-700" data-in-store-balance-due>—</dd>
            </div>
        </dl>

        <p id="order-confirm-cash-error" class="mt-2 hidden text-sm text-red-600" data-in-store-cash-error></p>
    </div>
@endif
