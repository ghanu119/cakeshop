import { getCsrfToken, refreshCsrfToken } from './csrf-token';

export function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');
}

export function messageFor(messages, key, fallback) {
    return messages[key] || fallback || 'Something went wrong. Please try again.';
}

export function loadRazorpayScript() {
    return new Promise((resolve, reject) => {
        if (window.Razorpay) {
            resolve(window.Razorpay);
            return;
        }

        const script = document.createElement('script');
        script.src = 'https://checkout.razorpay.com/v1/checkout.js';
        script.async = true;
        script.onload = () => resolve(window.Razorpay);
        script.onerror = () => reject(new Error('script_load_failed'));
        document.head.appendChild(script);
    });
}

async function postRequest(url, init, csrfToken, retryOnCsrf = true) {
    const token = csrfToken || getCsrfToken();
    const response = await fetch(url, {
        ...init,
        headers: {
            ...init.headers,
            'X-CSRF-TOKEN': token,
        },
        credentials: 'same-origin',
    });

    if (response.status === 419 && retryOnCsrf && refreshCsrfToken()) {
        return postRequest(url, init, getCsrfToken(), false);
    }

    let payload = {};
    try {
        payload = await response.json();
    } catch {
        payload = {};
    }

    return { response, payload };
}

export async function postJson(url, body, csrfToken = '') {
    return postRequest(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(body),
    }, csrfToken);
}

export async function postForm(url, formData, csrfToken = '') {
    return postRequest(url, {
        method: 'POST',
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: formData,
    }, csrfToken);
}

export async function openRazorpayModal({
    checkoutPayload,
    messages = {},
    description = 'Order payment',
    csrfToken = '',
    onSuccess,
    onDismiss,
    onFailure,
}) {
    try {
        const Razorpay = await loadRazorpayScript();

        const options = {
            key: checkoutPayload.key_id,
            amount: checkoutPayload.amount,
            currency: checkoutPayload.currency,
            name: document.title || 'Order payment',
            description,
            order_id: checkoutPayload.gateway_order_id,
            prefill: {
                name: checkoutPayload.customer_name || '',
                email: checkoutPayload.customer_email || '',
                contact: checkoutPayload.customer_phone || '',
            },
            handler: (response) => {
                onSuccess?.(response, checkoutPayload);
            },
            modal: {
                ondismiss: () => {
                    onDismiss?.(messageFor(messages, 'payment_cancelled', 'Payment was cancelled.'));
                },
            },
        };

        const razorpay = new Razorpay(options);
        razorpay.on('payment.failed', () => {
            onFailure?.(messageFor(messages, 'payment_failed', 'Your payment did not go through.'));
        });
        razorpay.open();
    } catch {
        onFailure?.(messageFor(messages, 'network_error', 'Could not load payment service.'));
    }
}
