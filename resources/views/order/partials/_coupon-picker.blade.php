@php

    $currency = settings('currency') ?? 'INR';

    $symbol = $currency === 'INR' ? '₹' : $currency . ' ';

    $showPicker = ($universalCoupons ?? collect())->count() >= 2;

    $initialCouponCode = old('coupon_code', $defaultCouponCode ?? '');

    $initialCodeApplied = filled($initialCouponCode) && ! old('coupon_declined');

@endphp



<div class="space-y-4" data-order-coupon-section data-validate-url="{{ route('order.product.validate-coupon', $product) }}" data-csrf="{{ csrf_token() }}" data-default-coupon-id="{{ $defaultCouponId ?? '' }}" data-default-coupon-code="{{ $defaultCouponCode ?? '' }}" data-currency-symbol="{{ $symbol }}" data-max-discount-template="{{ __('Maximum discount of :amount applies to this offer.', ['amount' => ':amount']) }}" data-recommended-label="{{ __('Recommended') }}" data-save-label="{{ __('Save') }}" data-available-offers-label="{{ __('Available offers') }}" data-pick-one-label="{{ __('Pick one') }}">

    <input type="hidden" name="coupon_declined" value="{{ old('coupon_declined', '0') }}" data-coupon-declined>

    <style>

        [data-order-coupon-section] .coupon-code-row { display: flex; align-items: stretch; gap: 0.5rem; }

        [data-order-coupon-section] .coupon-code-wrap { position: relative; flex: 1 1 0%; min-width: 0; border: 1px solid #e7e5e4; border-radius: 0.75rem; background: #fafaf9; }

        [data-order-coupon-section] .coupon-code-wrap:focus-within { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.15); }

        [data-order-coupon-section] .coupon-code-wrap.is-applied { border-color: #6ee7b7; background: #ecfdf5; box-shadow: 0 0 0 3px rgba(167, 243, 208, 0.45); }

        [data-order-coupon-section] .coupon-code-input { display: block; width: 100%; box-sizing: border-box; border: 0; background: transparent; padding: 0.625rem 2.25rem 0.625rem 0.875rem; font-size: 0.875rem; line-height: 1.25rem; text-transform: uppercase; color: #1c1917; outline: none; margin: 0; }

        [data-order-coupon-section] .coupon-code-input::placeholder { text-transform: none; color: #a8a29e; }

        [data-order-coupon-section] .coupon-code-clear { position: absolute; top: 50%; right: 0.5rem; z-index: 3; display: flex; align-items: center; justify-content: center; width: 1.25rem; height: 1.25rem; margin: 0; padding: 0; border: 0; border-radius: 9999px; background: #d6d3d1; color: #57534e; font-size: 1rem; line-height: 1; cursor: pointer; transform: translateY(-50%); }

        [data-order-coupon-section] .coupon-code-clear:hover { background: #a8a29e; color: #1c1917; }

        [data-order-coupon-section] .coupon-code-clear.is-hidden { display: none !important; }

        [data-order-coupon-section] .coupon-code-apply { flex-shrink: 0; align-self: stretch; min-width: 4.5rem; border: 1px solid #f59e0b; border-radius: 0.75rem; background: #f59e0b; padding: 0 1rem; font-size: 0.875rem; font-weight: 700; color: #fff; cursor: pointer; }

        [data-order-coupon-section] .coupon-code-apply:hover:not(:disabled) { background: #d97706; border-color: #d97706; }

        [data-order-coupon-section] .coupon-code-apply.is-applied, [data-order-coupon-section] .coupon-code-apply.is-applied:hover { background: #059669; border-color: #059669; }

        [data-order-coupon-section] .coupon-code-apply:disabled { cursor: default; opacity: 1; }

        [data-order-coupon-section] .coupon-max-discount-info { position: relative; display: inline-flex; flex-shrink: 0; vertical-align: middle; }

        [data-order-coupon-section] .coupon-max-discount-info__trigger { margin: 0; padding: 0; border: 0; background: transparent; cursor: pointer; line-height: 0; }

        [data-order-coupon-section] .coupon-max-discount-info__popover {

            position: absolute;

            bottom: calc(100% + 0.375rem);

            left: 50%;

            z-index: 50;

            display: none;

            width: max-content;

            max-width: 11.5rem;

            padding: 0.5rem 0.625rem;

            border-radius: 0.5rem;

            background: #1c1917;

            color: #fafaf9;

            font-size: 0.6875rem;

            font-weight: 500;

            line-height: 1.4;

            text-align: center;

            white-space: normal;

            box-shadow: 0 4px 14px rgba(28, 25, 23, 0.18);

            transform: translateX(-50%);

            pointer-events: none;

        }

        [data-order-coupon-section] .coupon-max-discount-info__popover::after {

            content: '';

            position: absolute;

            top: 100%;

            left: 50%;

            margin-left: -0.25rem;

            border: 0.25rem solid transparent;

            border-top-color: #1c1917;

        }

        [data-order-coupon-section] .coupon-max-discount-info.is-open .coupon-max-discount-info__popover { display: block; }

    </style>

    @if($showPicker)

        <div class="overflow-hidden rounded-2xl border border-stone-200/80 bg-white shadow-sm" data-coupon-offers-panel>

            <div class="flex items-center justify-between gap-3 border-b border-stone-100 bg-gradient-to-r from-amber-50/80 to-white px-4 py-3">

                <div class="flex items-center gap-2.5">

                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm" aria-hidden="true">

                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>

                    </span>

                    <p class="text-sm font-bold text-stone-900">{{ __('Available offers') }}</p>

                </div>

                <span class="text-[11px] font-semibold uppercase tracking-wide text-stone-400">{{ __('Pick one') }}</span>

            </div>



            <div class="divide-y divide-stone-100" role="radiogroup" aria-label="{{ __('Available offers') }}" data-coupon-offers-list>

                @foreach($universalCoupons as $offer)

                    @php

                        $isSelected = strtoupper((string) $initialCouponCode) === strtoupper((string) $offer['code']);

                    @endphp

                    <label class="group relative flex cursor-pointer gap-3 px-4 py-3.5 transition-colors duration-150 hover:bg-stone-50/80 has-[:checked]:bg-amber-50/60">

                        <span class="absolute inset-y-0 left-0 w-1 rounded-r bg-amber-500 opacity-0 transition-opacity group-has-[:checked]:opacity-100" aria-hidden="true"></span>

                        <input

                            type="radio"

                            name="coupon_offer"

                            value="{{ $offer['code'] }}"

                            class="mt-0.5 h-4 w-4 shrink-0 border-stone-300 text-amber-600 focus:ring-amber-500"

                            @checked($isSelected)

                            data-coupon-radio

                            data-coupon-code="{{ $offer['code'] }}"

                            data-discount-amount="{{ $offer['discount_amount'] }}"

                        />

                        <span class="min-w-0 flex-1">

                            <span class="flex items-start justify-between gap-3">

                                <span class="min-w-0">

                                    <span class="flex flex-wrap items-center gap-x-1.5 gap-y-1">

                                        <span class="font-semibold leading-tight text-stone-900">{{ $offer['label'] }}</span>

                                        @include('order.partials._coupon-max-discount-info', [

                                            'maxCap' => $offer['max_cap'] ?? null,

                                            'symbol' => $symbol,

                                        ])

                                        @if((string) ($defaultCouponId ?? '') === (string) $offer['id'])

                                            <span class="inline-flex items-center rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-800">{{ __('Recommended') }}</span>

                                        @endif

                                    </span>

                                    @if($offer['description'])

                                        <span class="mt-1 block text-xs leading-relaxed text-stone-500">{{ $offer['description'] }}</span>

                                    @endif

                                </span>

                                <span class="shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">{{ $offer['badge_text'] }}</span>

                            </span>

                            <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">

                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>

                                {{ __('Save') }} {{ $symbol }}{{ number_format($offer['discount_amount'], 2) }}

                            </span>

                        </span>

                    </label>

                @endforeach

            </div>

        </div>

    @else

        <div class="hidden" data-coupon-offers-panel aria-hidden="true"></div>

    @endif



    <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm">

        <div class="mb-3 flex items-center justify-between gap-2">

            <label for="coupon_code" class="text-sm font-bold text-stone-900">{{ __('Have a code?') }}</label>

            <span

                @class([

                    'items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-0.5 text-[11px] font-bold uppercase tracking-wide text-emerald-800',

                    'hidden' => ! $initialCodeApplied,

                    'inline-flex' => $initialCodeApplied,

                ])

                data-coupon-applied-badge

            >

                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>

                {{ __('Applied') }}

            </span>

        </div>



        <div class="coupon-code-row">

            <div @class(['coupon-code-wrap', 'is-applied' => $initialCodeApplied]) data-coupon-code-wrap>

                <input

                    type="text"

                    name="coupon_code"

                    id="coupon_code"

                    value="{{ $initialCouponCode }}"

                    class="coupon-code-input"

                    placeholder="{{ __('Enter code') }}"

                    data-coupon-code-input

                    autocomplete="off"

                />

                <button

                    type="button"

                    @class(['coupon-code-clear', 'is-hidden' => ! filled($initialCouponCode)])

                    data-coupon-code-clear

                    aria-label="{{ __('Clear coupon code') }}"

                >&times;</button>

            </div>

            <button

                type="button"

                @class(['coupon-code-apply', 'is-applied' => $initialCodeApplied])

                data-coupon-apply-btn

                data-label-apply="{{ __('Apply') }}"

                data-label-applied="{{ __('Applied') }}"

                @disabled($initialCodeApplied)

                aria-pressed="{{ $initialCodeApplied ? 'true' : 'false' }}"

            >

                {{ $initialCodeApplied ? __('Applied') : __('Apply') }}

            </button>

        </div>



        <p class="mt-2 text-xs text-stone-500">{{ __('Select an offer above or enter a code. Clear the field to remove the discount.') }}</p>

        @if(empty($customer))
            <p class="mt-1 text-xs text-stone-500">{{ __('Personal coupons require signing in to your account.') }}</p>
        @endif

        <p class="mt-1.5 hidden text-xs font-medium" data-coupon-code-feedback role="status" aria-live="polite"></p>

        @error('coupon_code')<p class="mt-1.5 text-sm text-red-600">{{ $message }}</p>@enderror

    </div>



    <div class="rounded-2xl border border-stone-200/80 bg-white p-4 shadow-sm" data-order-summary>

        <p class="mb-3 text-[11px] font-bold uppercase tracking-wider text-stone-400">{{ __('Price summary') }}</p>

        <div class="space-y-2 text-sm">

            <div class="flex justify-between text-stone-600">

                <span>{{ __('Subtotal') }}</span>

                <span class="font-medium text-stone-900" data-summary-subtotal>{{ $symbol }}0.00</span>

            </div>

            <div class="flex justify-between text-emerald-700 hidden" data-summary-discount-row>

                <span class="inline-flex items-center gap-1">

                    <span data-summary-discount-label>{{ __('Discount') }}</span>

                    <span class="hidden" data-summary-max-discount-wrap>

                        <span class="coupon-max-discount-info relative inline-flex shrink-0 align-middle" data-max-discount-info>

                            <button

                                type="button"

                                class="coupon-max-discount-info__trigger inline-flex h-4 w-4 items-center justify-center rounded-full text-emerald-600 hover:text-emerald-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500/50"

                                data-max-discount-trigger

                                aria-expanded="false"

                                aria-label="{{ __('Maximum discount information') }}"

                            >

                                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">

                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />

                                </svg>

                            </button>

                            <span class="coupon-max-discount-info__popover" data-max-discount-popover role="tooltip"></span>

                        </span>

                    </span>

                </span>

                <span class="font-semibold" data-summary-discount>−{{ $symbol }}0.00</span>

            </div>

        </div>

        <div class="mt-3 flex justify-between border-t border-dashed border-stone-200 pt-3 text-base font-bold text-stone-900">

            <span>{{ __('Total') }}</span>

            <span class="text-amber-700" data-summary-total>{{ $symbol }}0.00</span>

        </div>

    </div>

</div>

