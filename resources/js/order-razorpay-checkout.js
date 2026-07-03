import {
    escapeHtml,
    messageFor,
    openRazorpayModal,
    postJson,
} from './order-razorpay-core';
import { getCsrfToken } from './csrf-token';

document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-razorpay-checkout]');
    if (!root) return;

    const payBtn = root.querySelector('[data-razorpay-pay-btn]');
    const payLabel = root.querySelector('[data-razorpay-pay-label]');
    const errorContainer = document.getElementById('payment-error-container');


    let messages = {};
    try {
        messages = JSON.parse(root.dataset.messages || '{}');
    } catch {
        messages = {};
    }

    const showError = (message, retryable = true) => {
        if (!errorContainer) return;

        errorContainer.innerHTML = `
            <div class="rounded-2xl border border-amber-200 bg-amber-50/90 px-5 py-4 text-sm text-stone-800" role="alert">
                <p>${escapeHtml(message)}</p>
                ${retryable ? `<button type="button" data-payment-error-retry class="mt-3 inline-flex items-center rounded-full border border-amber-300 bg-white px-4 py-2 text-xs font-semibold text-amber-900 hover:bg-amber-50">${escapeHtml(messageFor(messages, 'try_again', 'Try again'))}</button>` : ''}
            </div>
        `;

        const retryBtn = errorContainer.querySelector('[data-payment-error-retry]');
        if (retryBtn) {
            retryBtn.addEventListener('click', () => {
                errorContainer.innerHTML = '';
                startCheckout();
            });
        }
    };

    const setLoading = (loading) => {
        if (!payBtn) return;
        payBtn.disabled = loading;
        if (payLabel) {
            payLabel.textContent = loading
                ? messageFor(messages, 'processing', 'Processing payment…')
                : messageFor(messages, 'pay_now', 'Pay now');
        }
    };

    const verifyPayment = async (checkoutPayload, razorpayResponse) => {
        const verifyUrl = root.dataset.verifyUrl;
        const { response, payload } = await postJson(verifyUrl, {
            razorpay_order_id: razorpayResponse.razorpay_order_id,
            razorpay_payment_id: razorpayResponse.razorpay_payment_id,
            razorpay_signature: razorpayResponse.razorpay_signature,
        }, getCsrfToken());

        if (response.ok && payload.success) {
            window.location.reload();
            return;
        }

        showError(payload.message || messageFor(messages, 'network_error', 'Something went wrong.'), payload.retryable !== false);
    };

    const startCheckout = async () => {
        if (!payBtn) return;

        setLoading(true);
        if (errorContainer) {
            errorContainer.innerHTML = '';
        }

        try {
            const initiateUrl = root.dataset.initiateUrl;
            const { response, payload } = await postJson(initiateUrl, {}, getCsrfToken());

            if (!response.ok || !payload.success) {
                setLoading(false);
                showError(payload.message || messageFor(messages, 'network_error', 'Something went wrong.'), payload.retryable !== false);
                return;
            }

            await openRazorpayModal({
                checkoutPayload: payload,
                messages,
                description: `Order ${root.dataset.orderUuid}`,
                csrfToken: getCsrfToken(),
                onSuccess: (response) => {
                    verifyPayment(payload, response).catch(() => {
                        setLoading(false);
                        showError(messageFor(messages, 'network_error', 'Something went wrong.'), true);
                    });
                },
                onDismiss: (message) => {
                    setLoading(false);
                    showError(message, true);
                },
                onFailure: (message) => {
                    setLoading(false);
                    showError(message, true);
                },
            });
            setLoading(false);
        } catch {
            setLoading(false);
            showError(messageFor(messages, 'network_error', 'Something went wrong.'), true);
        }
    };

    payBtn?.addEventListener('click', () => {
        startCheckout();
    });
});
