export function extractApiValidationError(payload) {
    if (payload?.errors && typeof payload.errors === 'object') {
        const entries = Object.entries(payload.errors);

        if (entries.length > 0) {
            const [field, messages] = entries[0];
            const message = Array.isArray(messages) ? messages[0] : messages;

            if (message) {
                return { message: String(message), field: String(field) };
            }
        }
    }

    return {
        message: payload?.message || 'Please fix the errors below and try again.',
        field: null,
    };
}

export async function validateCouponsForCheckout(form, csrfToken) {
    const section = form.querySelector('[data-order-coupon-section]');

    if (!section) {
        return { ok: true };
    }

    const validateUrl = section.dataset.validateUrl;

    if (!validateUrl) {
        return { ok: true };
    }

    if (section.querySelector('[data-coupon-declined]')?.value === '1') {
        return { ok: true };
    }

    const codeInput = section.querySelector('[data-coupon-code-input]');
    const applyBtn = section.querySelector('[data-coupon-apply-btn]');
    const manualCode = codeInput?.value?.trim() || '';
    const isApplied = applyBtn?.classList.contains('is-applied');

    const body = new URLSearchParams();
    const variantInput = form.querySelector('#product_variant_id');
    const quantityInput = form.querySelector('#quantity');
    const emailInput = form.querySelector('#guest_email');

    if (variantInput?.value) {
        body.set('product_variant_id', variantInput.value);
    }

    body.set('quantity', quantityInput?.value || '1');

    if (emailInput?.value?.trim()) {
        body.set('guest_email', emailInput.value.trim());
    }

    if (manualCode) {
        body.set('coupon_code', manualCode);
    } else {
        body.set('auto_select_best', '1');
    }

    const response = await fetch(validateUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            Accept: 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body.toString(),
        credentials: 'same-origin',
    });

    let data = {};

    try {
        data = await response.json();
    } catch {
        data = {};
    }

    const hasCouponIntent = Boolean(manualCode) || isApplied || body.has('auto_select_best');

    if (!hasCouponIntent) {
        return { ok: true, total: data.total ?? null, subtotal: data.subtotal ?? null };
    }

    if (manualCode || isApplied) {
        if (!data.valid) {
            return {
                ok: false,
                message: data.message || 'This coupon code is invalid or not applicable to your order.',
                field: 'coupon_code',
            };
        }

        return { ok: true, total: data.total ?? null, subtotal: data.subtotal ?? null };
    }

    if (body.has('auto_select_best') && data.message && !data.valid) {
        return {
            ok: false,
            message: data.message,
            field: 'coupon_code',
        };
    }

    return { ok: true, total: data.total ?? null, subtotal: data.subtotal ?? null };
}
