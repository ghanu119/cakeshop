@once
    @push('styles')
    <style>
        #order-place-confirm-modal {
            position: fixed;
            inset: 0;
            z-index: 9999;
            display: none;
            overflow-x: hidden;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            padding: 1rem;
            padding-bottom: max(1rem, env(safe-area-inset-bottom));
            box-sizing: border-box;
        }

        #order-place-confirm-modal.is-open {
            display: block;
        }

        #order-place-confirm-modal .order-confirm-backdrop {
            position: fixed;
            inset: 0;
            background-color: rgba(28, 25, 23, 0.55);
            backdrop-filter: blur(4px);
        }

        #order-place-confirm-modal .order-confirm-panel {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 28rem;
            max-height: calc(100vh - 2rem);
            margin: 1.5rem auto;
            overflow-y: auto;
            border-radius: 1rem;
            border: 1px solid #fde68a;
            background-color: #fff;
            padding: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            box-sizing: border-box;
        }

        #order-place-confirm-modal .order-confirm-panel button {
            box-shadow: none;
        }

        @media (min-width: 640px) {
            #order-place-confirm-modal .order-confirm-panel {
                margin: 2rem auto;
                padding: 2rem;
            }
        }

        @media (max-height: 640px) {
            #order-place-confirm-modal .order-confirm-panel {
                margin-top: 0.75rem;
                margin-bottom: 0.75rem;
            }
        }

        #order-place-confirm-modal.is-processing .order-confirm-backdrop {
            pointer-events: none;
        }

        #order-place-confirm-modal.is-processing .order-confirm-panel > :not(#order-confirm-processing) {
            pointer-events: none;
            user-select: none;
        }

        #order-place-confirm-modal.is-processing #order-confirm-review-step {
            visibility: hidden;
        }

        #order-confirm-processing {
            position: absolute;
            inset: 0;
            z-index: 20;
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            min-height: 100%;
            border-radius: 1rem;
            background-color: #fff;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        #order-place-confirm-modal.is-processing #order-confirm-processing {
            display: flex;
        }

        #order-confirm-processing .order-confirm-spinner {
            width: 2.75rem;
            height: 2.75rem;
            border: 3px solid #fde68a;
            border-top-color: #f59e0b;
            border-radius: 9999px;
            animation: order-confirm-spin 0.75s linear infinite;
        }

        @keyframes order-confirm-spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
    @endpush
@endonce

@php
    $checkoutPaymentConfig = $checkoutPaymentConfig ?? [
        'pay_before_order' => false,
        'prepare_url' => '',
        'finalize_url' => '',
        'enabled' => false,
    ];
    $currency = settings('currency') ?? 'INR';
    $currencySymbol = $currency === 'INR' ? '₹' : $currency . ' ';
    $payBeforeOrder = ! empty($checkoutPaymentConfig['pay_before_order']) && ! empty($checkoutPaymentConfig['enabled']);
    $isImpersonating = $isImpersonating ?? false;
    $paymentMessages = [
        'payment_cancelled' => __('Payment was cancelled. You can try again or go back to edit your order.'),
        'payment_failed' => __('payments.errors.payment_failed'),
        'network_error' => __('payments.errors.network_error'),
        'processing' => __('payments.processing'),
        'try_again' => __('payments.try_again'),
        'pay_now' => __('payments.pay_now'),
        'payment_description' => __('Secure payment for your order'),
        'order_processing' => __('Payment received! We are placing your order…'),
        'order_processing_hint' => __('Please wait while we confirm your order.'),
    ];
@endphp

<div
    id="order-place-confirm-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="order-place-confirm-title"
    aria-hidden="true"
    data-send-otp-url="{{ route('order.checkout.send-otp') }}"
    data-verify-otp-url="{{ route('order.checkout.verify-otp') }}"
    data-otp-required-message="{{ __('Please enter the 6-digit verification code.') }}"
    data-otp-sending-message="{{ __('Sending verification code...') }}"
    data-otp-verifying-label="{{ __('Verifying...') }}"
    data-pay-before-order="{{ $payBeforeOrder ? '1' : '0' }}"
    data-is-impersonating="{{ $isImpersonating ? '1' : '0' }}"
    data-prepare-url="{{ $checkoutPaymentConfig['prepare_url'] ?? '' }}"
    data-finalize-url="{{ $checkoutPaymentConfig['finalize_url'] ?? '' }}"
    data-finalize-free-url="{{ $checkoutPaymentConfig['finalize_free_url'] ?? '' }}"
    data-currency-symbol="{{ $currencySymbol }}"
    data-pay-label-prefix="{{ __('Pay') }}"
    data-messages='@json($paymentMessages)'
    data-whatsapp-enabled="{{ whatsapp_login_enabled() ? '1' : '0' }}"
    data-otp-status-email="{{ __('Enter the verification code we sent to your email.') }}"
    data-otp-status-whatsapp="{{ __('Enter the verification code we sent to your WhatsApp.') }}"
    data-otp-missing-email="{{ __('Please add an email address to verify by email.') }}"
>
    <div class="order-confirm-backdrop" data-order-confirm-backdrop></div>
    <div class="order-confirm-panel">
        <div id="order-confirm-processing" aria-live="polite" aria-busy="true" hidden>
            <div class="order-confirm-spinner" aria-hidden="true"></div>
            <div>
                <p data-processing-message class="text-base font-bold text-stone-900">{{ __('Payment received! We are placing your order…') }}</p>
                <p data-processing-hint class="mt-2 text-sm text-stone-600">{{ __('Please wait while we confirm your order.') }}</p>
            </div>
        </div>
        <div id="order-confirm-review-step">
            <h2 id="order-place-confirm-title" class="text-xl font-bold text-stone-900 sm:text-2xl">
                {{ $payBeforeOrder ? __('Review your order') : __('Confirm your order') }}
            </h2>
            <p class="mt-2 text-sm text-stone-600">
                @if($payBeforeOrder)
                    {{ __('Verify your details below. You will pay securely before we place your order.') }}
                @else
                    {{ __('Please verify these choices before placing your order.') }}
                @endif
            </p>

            <dl class="mt-6 space-y-4">
                <div id="order-confirm-weight-row" class="hidden rounded-xl border border-amber-200 bg-amber-50/80 px-4 py-3">
                    <dt class="text-xs font-bold uppercase tracking-wider text-amber-700">{{ __('Weight') }}</dt>
                    <dd id="order-confirm-weight" class="mt-1 text-base font-bold text-amber-900">—</dd>
                </div>
                <div id="order-confirm-flavor-row" class="hidden rounded-xl border border-rose-200 bg-rose-50/80 px-4 py-3">
                    <dt class="text-xs font-bold uppercase tracking-wider text-rose-700">{{ __('Flavor') }}</dt>
                    <dd id="order-confirm-flavor" class="mt-1 text-base font-bold text-rose-900">—</dd>
                </div>
                <div id="order-confirm-order-type-row" class="hidden rounded-xl border border-teal-200 bg-teal-50/80 px-4 py-3">
                    <dt class="text-xs font-bold uppercase tracking-wider text-teal-700">{{ __('Order type') }}</dt>
                    <dd id="order-confirm-order-type" class="mt-1 text-base font-bold text-teal-900">—</dd>
                </div>
            </dl>

            <div id="order-confirm-otp-section" class="mt-6 hidden rounded-xl border border-stone-200 bg-stone-50 p-4">
                @if(whatsapp_login_enabled())
                    <div data-otp-channel-toggle class="mb-3 grid grid-cols-2 gap-2 rounded-lg border border-stone-200 bg-white p-1">
                        <button type="button" data-otp-channel="whatsapp" class="rounded-md px-3 py-2 text-sm font-medium transition">{{ __('WhatsApp') }}</button>
                        <button type="button" data-otp-channel="email" class="rounded-md px-3 py-2 text-sm font-medium transition">{{ __('Email') }}</button>
                    </div>
                @endif
                <p id="order-confirm-otp-status" class="text-sm text-stone-600">{{ __('Enter the verification code we sent to your email.') }}</p>
                <label for="order-confirm-otp-code" class="mb-1 mt-3 block text-sm font-medium text-stone-700">{{ __('Verification code') }}</label>
                <input
                    type="text"
                    id="order-confirm-otp-code"
                    inputmode="numeric"
                    maxlength="6"
                    autocomplete="one-time-code"
                    class="block w-full rounded-lg border-stone-300 text-center text-lg tracking-[0.3em] shadow-sm focus:border-amber-500 focus:ring-amber-500"
                    placeholder="000000"
                />
                <p id="order-confirm-otp-error" class="mt-2 hidden text-sm text-red-600"></p>
                <button type="button" data-order-confirm-resend-otp class="mt-2 text-sm text-amber-700 hover:underline">
                    {{ __('Resend code') }}
                </button>
            </div>

            <div id="order-confirm-review-error" class="mt-4 hidden"></div>

            @include('order.partials._in-store-cash-fields', [
                'isImpersonating' => $isImpersonating,
                'currencySymbol' => $currencySymbol,
            ])

            @if($payBeforeOrder)
                <p class="mt-4 text-xs leading-relaxed text-stone-500">
                    {{ __('Secured by Razorpay. UPI, cards, and net banking accepted.') }}
                </p>
            @endif

            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-end sm:gap-4">
                <button
                    type="button"
                    data-order-confirm-submit
                    class="inline-flex min-h-[3rem] w-full shrink-0 items-center justify-center gap-2 whitespace-nowrap rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-sm font-bold text-white transition hover:from-amber-600 hover:to-orange-600 disabled:cursor-not-allowed disabled:opacity-70 sm:order-2 sm:w-auto sm:min-w-[14rem]"
                >
                    @if($payBeforeOrder)
                        <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span data-order-confirm-submit-label>{{ __('Pay now') }}</span>
                    @else
                        {{ __('Yes, place order') }}
                    @endif
                </button>
                <button
                    type="button"
                    data-order-confirm-cancel
                    class="inline-flex min-h-[3rem] w-full shrink-0 items-center justify-center whitespace-nowrap rounded-xl border border-stone-200 bg-white px-6 py-3 text-sm font-bold text-stone-700 transition hover:border-stone-300 hover:bg-stone-50 sm:order-1 sm:w-auto sm:min-w-[10rem]"
                >
                    {{ __('Go back and edit') }}
                </button>
            </div>
        </div>
    </div>
</div>

@once
    @push('scripts')
        @vite(['resources/js/order-place-confirm.js'])
    @endpush
@endonce
