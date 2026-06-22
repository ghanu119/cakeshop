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
            margin: 1.5rem auto;
            border-radius: 1rem;
            border: 1px solid #fde68a;
            background-color: #fff;
            padding: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            box-sizing: border-box;
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
    </style>
    @endpush
@endonce

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
>
    <div class="order-confirm-backdrop" data-order-confirm-backdrop></div>
    <div class="order-confirm-panel">
        <h2 id="order-place-confirm-title" class="text-xl font-bold text-stone-900 sm:text-2xl">
            {{ __('Confirm your order') }}
        </h2>
        <p class="mt-2 text-sm text-stone-600">
            {{ __('Please verify these choices before placing your order.') }}
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

        <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <button
                type="button"
                data-order-confirm-cancel
                class="w-full rounded-full border-2 border-stone-200 px-6 py-3 text-sm font-bold text-stone-700 transition hover:border-stone-300 hover:bg-stone-50 sm:w-auto"
            >
                {{ __('Go back and edit') }}
            </button>
            <button
                type="button"
                data-order-confirm-submit
                class="w-full rounded-full bg-gradient-to-r from-amber-500 to-orange-500 px-6 py-3 text-sm font-bold text-white shadow-md transition hover:shadow-lg sm:w-auto"
            >
                {{ __('Yes, place order') }}
            </button>
        </div>
    </div>
</div>

@once
    @push('scripts')
        @vite(['resources/js/order-place-confirm.js'])
    @endpush
@endonce
