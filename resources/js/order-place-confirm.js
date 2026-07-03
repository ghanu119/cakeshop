import {
    extractApiValidationError,
    validateCouponsForCheckout,
} from './order-checkout-validate';
import { getCsrfToken, refreshCsrfToken } from './csrf-token';
import {
    escapeHtml,
    messageFor,
    openRazorpayModal,
    postForm,
    postJson,
} from './order-razorpay-core';

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('order-place-confirm-modal');
    if (!modal) {
        return;
    }

    if (modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const backdrop = modal.querySelector('[data-order-confirm-backdrop]');
    const cancelBtn = modal.querySelector('[data-order-confirm-cancel]');
    const submitBtn = modal.querySelector('[data-order-confirm-submit]');
    const submitLabel = modal.querySelector('[data-order-confirm-submit-label]');
    const resendBtn = modal.querySelector('[data-order-confirm-resend-otp]');
    const reviewError = document.getElementById('order-confirm-review-error');
    const processingOverlay = document.getElementById('order-confirm-processing');
    const processingMessage = processingOverlay?.querySelector('[data-processing-message]');
    const processingHint = processingOverlay?.querySelector('[data-processing-hint]');
    const weightRow = document.getElementById('order-confirm-weight-row');
    const flavorRow = document.getElementById('order-confirm-flavor-row');
    const orderTypeRow = document.getElementById('order-confirm-order-type-row');
    const weightValue = document.getElementById('order-confirm-weight');
    const flavorValue = document.getElementById('order-confirm-flavor');
    const orderTypeValue = document.getElementById('order-confirm-order-type');
    const otpSection = document.getElementById('order-confirm-otp-section');
    const otpInput = document.getElementById('order-confirm-otp-code');
    const otpStatus = document.getElementById('order-confirm-otp-status');
    const otpError = document.getElementById('order-confirm-otp-error');
    const sendOtpUrl = modal.dataset.sendOtpUrl;
    const verifyOtpUrl = modal.dataset.verifyOtpUrl;
    const otpRequiredMessage = modal.dataset.otpRequiredMessage ?? 'Please enter the 6-digit verification code.';
    const otpSendingMessage = modal.dataset.otpSendingMessage ?? 'Sending verification code...';
    const verifyingLabel = modal.dataset.otpVerifyingLabel ?? 'Verifying...';
    const payBeforeOrder = modal.dataset.payBeforeOrder === '1';
    const prepareUrl = modal.dataset.prepareUrl ?? '';
    const finalizeUrl = modal.dataset.finalizeUrl ?? '';
    const payLabelPrefix = modal.dataset.payLabelPrefix ?? 'Pay';
    const currencySymbol = modal.dataset.currencySymbol ?? '₹';

    let messages = {};
    try {
        messages = JSON.parse(modal.dataset.messages || '{}');
    } catch {
        messages = {};
    }

    function getPlaceOrderErrorEl(form) {
        return form?.querySelector('[data-order-place-error]') ?? document.querySelector('[data-order-place-error]');
    }

    function clearPlaceOrderError(form) {
        const errorEl = getPlaceOrderErrorEl(form);

        if (!errorEl) {
            return;
        }

        errorEl.textContent = '';
        errorEl.classList.add('hidden');
    }

    function showPlaceOrderError(form, message) {
        const errorEl = getPlaceOrderErrorEl(form);

        if (!errorEl || !message) {
            return;
        }

        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
    }

    function scrollToPlaceOrderError(form) {
        const target = getPlaceOrderErrorEl(form) ?? getPlaceOrderButton(form);

        target?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function setPlaceOrderButtonLoading(form, loading) {
        const btn = getPlaceOrderButton(form);

        if (!btn) {
            return;
        }

        btn.disabled = loading;
        btn.dataset.originalLabel ??= btn.textContent;
        btn.textContent = loading
            ? (btn.dataset.submittingText || 'Checking…')
            : btn.dataset.originalLabel;
    }

    function notifyCouponInvalid(message) {
        document.dispatchEvent(new CustomEvent('order-coupon:invalidate', {
            detail: { message },
        }));
    }

    function handleCheckoutValidationFailure(form, message, field = null) {
        closeModal();
        showPlaceOrderError(form, message);

        if (field === 'coupon_code') {
            notifyCouponInvalid(message);
        }

        scrollToPlaceOrderError(form);
    }

    function getEstimatedTotalText(form) {
        const totalEl = form?.querySelector('[data-summary-total]')
            ?? form?.querySelector('#order-estimated-total');

        return totalEl?.textContent?.trim() || null;
    }

    function buildPayButtonLabel(form) {
        const total = getEstimatedTotalText(form);

        if (total) {
            return `${payLabelPrefix} ${total}`;
        }

        return messageFor(messages, 'pay_now', 'Pay now');
    }

    function updateSubmitPayLabel(form) {
        if (!payBeforeOrder || !submitBtn) {
            return;
        }

        const label = buildPayButtonLabel(form);
        submitBtn.dataset.originalLabel = label;

        if (submitLabel) {
            submitLabel.textContent = label;
        } else if (!submitBtn.disabled) {
            submitBtn.textContent = label;
        }
    }

    async function tryOpenCheckoutModal(form) {
        clearPlaceOrderError(form);
        setPlaceOrderButtonLoading(form, true);

        try {
            const couponResult = await validateCouponsForCheckout(form, getCsrfToken());

            if (!couponResult.ok) {
                showPlaceOrderError(form, couponResult.message);
                notifyCouponInvalid(couponResult.message);
                scrollToPlaceOrderError(form);
                return;
            }

            openModal(form);
        } catch (error) {
            showPlaceOrderError(form, error.message || 'Something went wrong. Please try again.');
            scrollToPlaceOrderError(form);
        } finally {
            setPlaceOrderButtonLoading(form, false);
        }
    }

    function getPlaceOrderButton(form) {
        return form.querySelector('button[type="submit"]');
    }

    function resetPlaceOrderButton(form) {
        const btn = getPlaceOrderButton(form);
        if (!btn) {
            return;
        }

        btn.disabled = false;
    }

    function getSelectedPillText(picker, attribute) {
        const selected = picker?.querySelector(`[${attribute}][aria-pressed="true"]`);
        return selected?.textContent?.trim() || '';
    }

    function getWeightLabel(form) {
        const summary = document.getElementById('order-summary-weight');
        if (summary?.textContent?.trim()) {
            return summary.textContent.trim();
        }

        const picker = form.querySelector('[data-variant-picker]');
        return getSelectedPillText(picker, 'data-variant-id');
    }

    function getFlavorLabel(form) {
        const summary = document.getElementById('order-summary-flavor');
        if (summary?.textContent?.trim()) {
            return summary.textContent.trim();
        }

        const picker = form.querySelector('[data-flavor-picker]');
        return getSelectedPillText(picker, 'data-flavor-id');
    }

    function getOrderTypeLabel() {
        const picker = document.querySelector('[data-fulfillment-picker]');
        const fromPill = getSelectedPillText(picker, 'data-fulfillment-type');
        if (fromPill) {
            return fromPill;
        }

        const hidden = document.getElementById('fulfillment_type');
        if (!hidden) {
            return '';
        }

        return hidden.value === 'delivery' ? 'Deliver' : 'Take away';
    }

    function setRow(row, valueEl, label, show) {
        if (!row || !valueEl) {
            return;
        }

        if (show && label) {
            valueEl.textContent = label;
            row.classList.remove('hidden');
        } else {
            row.classList.add('hidden');
        }
    }

    function isGuestCheckout(form) {
        return form.hasAttribute('data-guest-checkout');
    }

    function getGuestEmail(form) {
        return form.querySelector('#guest_email')?.value?.trim() ?? '';
    }

    function clearOtpError() {
        if (!otpError) {
            return;
        }

        otpError.textContent = '';
        otpError.classList.add('hidden');
    }

    function showOtpError(message) {
        if (!otpError) {
            return;
        }

        otpError.textContent = message;
        otpError.classList.remove('hidden');
    }

    function clearReviewError() {
        if (!reviewError) {
            return;
        }

        reviewError.innerHTML = '';
        reviewError.classList.add('hidden');
    }

    function showReviewError(message, retryable = true) {
        if (!reviewError) {
            return;
        }

        reviewError.innerHTML = `
            <div class="rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-sm text-stone-800" role="alert">
                <p>${escapeHtml(message)}</p>
                ${retryable ? `<button type="button" data-order-confirm-review-retry class="mt-2 text-xs font-semibold text-amber-800 underline underline-offset-2">${escapeHtml(messageFor(messages, 'try_again', 'Try again'))}</button>` : ''}
            </div>
        `;
        reviewError.classList.remove('hidden');

        reviewError.querySelector('[data-order-confirm-review-retry]')?.addEventListener('click', () => {
            clearReviewError();
            submitBtn?.click();
        });
    }

    function setSubmitLoading(loading) {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = loading;

        if (!submitBtn.dataset.originalLabel) {
            submitBtn.dataset.originalLabel = payBeforeOrder
                ? buildPayButtonLabel(activeForm ?? undefined)
                : (submitLabel?.textContent ?? submitBtn.textContent);
        }

        const label = loading
            ? (payBeforeOrder
                ? messageFor(messages, 'processing', 'Processing payment…')
                : verifyingLabel)
            : submitBtn.dataset.originalLabel;

        if (submitLabel) {
            submitLabel.textContent = label;
        } else {
            submitBtn.textContent = label;
        }
    }

    async function postJsonLocal(url, payload, retryOnCsrf = true) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            credentials: 'same-origin',
        });

        if (response.status === 419 && retryOnCsrf && refreshCsrfToken()) {
            return postJsonLocal(url, payload, false);
        }

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            const message = data.message
                || (data.errors ? Object.values(data.errors).flat()[0] : null)
                || 'Request failed';
            throw new Error(message);
        }

        return data;
    }

    async function sendCheckoutOtp(email) {
        const data = await postJsonLocal(sendOtpUrl, { email });
        if (otpStatus && data.message) {
            otpStatus.textContent = data.message;
        }
    }

    async function verifyCheckoutOtp(email, code) {
        await postJsonLocal(verifyOtpUrl, { email, code });
    }

    let activeForm = null;
    let confirmed = false;
    let otpSending = false;
    let checkoutPayload = null;
    let modalLocked = false;

    function setModalInteractiveControlsDisabled(disabled) {
        submitBtn && (submitBtn.disabled = disabled);
        cancelBtn && (cancelBtn.disabled = disabled);
        resendBtn && (resendBtn.disabled = disabled);

        if (otpInput) {
            otpInput.disabled = disabled;
            otpInput.readOnly = disabled;
        }
    }

    function setModalProcessing(processing, message = null) {
        modalLocked = processing;
        modal.classList.toggle('is-processing', processing);
        modal.setAttribute('aria-busy', processing ? 'true' : 'false');

        if (processingOverlay) {
            processingOverlay.hidden = !processing;
        }

        if (message && processingMessage) {
            processingMessage.textContent = message;
        }

        if (processingHint) {
            processingHint.textContent = messageFor(
                messages,
                'order_processing_hint',
                'Please wait while we confirm your order.',
            );
        }

        setModalInteractiveControlsDisabled(processing);

        if (activeForm) {
            const placeBtn = getPlaceOrderButton(activeForm);
            if (placeBtn) {
                placeBtn.disabled = processing;
            }
        }
    }

    function canCloseModal() {
        return !modalLocked;
    }

    async function prepareCheckout(form) {
        const formData = new FormData(form);
        const { response, payload } = await postForm(prepareUrl, formData, getCsrfToken());

        if (!response.ok || !payload.success) {
            const { message, field } = extractApiValidationError(payload);
            const error = new Error(message);
            error.field = field;
            throw error;
        }

        return payload;
    }

    async function finalizeCheckout(razorpayResponse) {
        const { response, payload } = await postJson(finalizeUrl, {
            checkout_reference: checkoutPayload.checkout_reference,
            razorpay_order_id: razorpayResponse.razorpay_order_id,
            razorpay_payment_id: razorpayResponse.razorpay_payment_id,
            razorpay_signature: razorpayResponse.razorpay_signature,
        }, getCsrfToken());

        if (response.ok && payload.success && payload.redirect_url) {
            window.location.href = payload.redirect_url;
            return;
        }

        throw new Error(payload.message || messageFor(messages, 'network_error', 'Something went wrong.'));
    }

    async function startPayAndPlaceOrder() {
        if (!activeForm || !payBeforeOrder) {
            return;
        }

        clearReviewError();
        setSubmitLoading(true);

        try {
            checkoutPayload = await prepareCheckout(activeForm);

            if (checkoutPayload.display_amount != null) {
                const formatted = `${currencySymbol}${Number(checkoutPayload.display_amount).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
                const serverLabel = `${payLabelPrefix} ${formatted}`;
                submitBtn.dataset.originalLabel = serverLabel;

                if (submitLabel) {
                    submitLabel.textContent = serverLabel;
                }
            }

            setSubmitLoading(false);

            await openRazorpayModal({
                checkoutPayload,
                messages,
                description: messageFor(messages, 'payment_description', 'Complete your order'),
                csrfToken: getCsrfToken(),
                onSuccess: async (response) => {
                    clearReviewError();
                    setModalProcessing(
                        true,
                        messageFor(messages, 'order_processing', 'Payment received! We are placing your order…'),
                    );
                    setSubmitLoading(true);

                    try {
                        await finalizeCheckout(response);
                    } catch (error) {
                        setModalProcessing(false);
                        setSubmitLoading(false);
                        showReviewError(error.message, true);
                    }
                },
                onDismiss: (message) => {
                    if (modalLocked) {
                        return;
                    }

                    showReviewError(message, true);
                },
                onFailure: (message) => {
                    if (modalLocked) {
                        return;
                    }

                    showReviewError(message, true);
                },
            });
        } catch (error) {
            setSubmitLoading(false);

            if (error.field) {
                handleCheckoutValidationFailure(activeForm, error.message, error.field);
                return;
            }

            showReviewError(error.message, true);
        }
    }

    async function prepareGuestOtp(form) {
        if (!otpSection || !otpInput) {
            return;
        }

        otpSection.classList.remove('hidden');
        otpInput.value = '';
        clearOtpError();

        const email = getGuestEmail(form);
        if (!email || otpSending) {
            return;
        }

        otpSending = true;
        if (otpStatus) {
            otpStatus.textContent = otpSendingMessage;
        }

        try {
            await sendCheckoutOtp(email);
        } catch (error) {
            showOtpError(error.message);
        } finally {
            otpSending = false;
        }
    }

    function openModal(form) {
        confirmed = false;
        checkoutPayload = null;
        modalLocked = false;
        activeForm = form;
        clearReviewError();
        setModalProcessing(false);

        const hasVariants = form.hasAttribute('data-has-variants');
        const hasFlavors = form.hasAttribute('data-has-flavors');
        const showOrderType = form.hasAttribute('data-show-order-type');
        const guest = isGuestCheckout(form);

        setRow(weightRow, weightValue, getWeightLabel(form), hasVariants);
        setRow(flavorRow, flavorValue, getFlavorLabel(form), hasFlavors);
        setRow(orderTypeRow, orderTypeValue, getOrderTypeLabel(), showOrderType);

        if (otpSection) {
            if (guest) {
                otpSection.classList.remove('hidden');
                void prepareGuestOtp(form);
            } else {
                otpSection.classList.add('hidden');
                clearOtpError();
                if (otpInput) {
                    otpInput.value = '';
                }
            }
        }

        if (submitBtn) {
            delete submitBtn.dataset.originalLabel;
        }

        updateSubmitPayLabel(form);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        (guest && otpInput ? otpInput : submitBtn)?.focus();
    }

    function closeModal() {
        if (!canCloseModal()) {
            return;
        }

        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        modal.setAttribute('aria-busy', 'false');
        document.body.classList.remove('overflow-hidden');
        setSubmitLoading(false);
        setModalProcessing(false);
        clearOtpError();
        clearReviewError();

        if (activeForm) {
            resetPlaceOrderButton(activeForm);
            activeForm = null;
        }
    }

    document.querySelectorAll('[data-order-place-confirm]').forEach((form) => {
        form.addEventListener(
            'submit',
            function (event) {
                if (confirmed) {
                    return;
                }

                if (!form.reportValidity()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();
                resetPlaceOrderButton(form);
                void tryOpenCheckoutModal(form);
            },
            true
        );
    });

    document.querySelectorAll('form[action*="order/product"]').forEach((form) => {
        if (form.hasAttribute('data-order-place-confirm')) {
            return;
        }

        if (!isGuestCheckout(form)) {
            return;
        }

        form.addEventListener(
            'submit',
            function (event) {
                if (confirmed) {
                    return;
                }

                if (!form.reportValidity()) {
                    return;
                }

                event.preventDefault();
                event.stopImmediatePropagation();
                resetPlaceOrderButton(form);
                void tryOpenCheckoutModal(form);
            },
            true
        );
    });

    submitBtn?.addEventListener('click', async function () {
        if (!activeForm || modalLocked) {
            return;
        }

        clearOtpError();
        clearReviewError();

        if (isGuestCheckout(activeForm)) {
            const email = getGuestEmail(activeForm);
            const code = otpInput?.value?.trim() ?? '';

            if (code.length !== 6) {
                showOtpError(otpRequiredMessage);
                otpInput?.focus();
                return;
            }

            setSubmitLoading(true);

            try {
                await verifyCheckoutOtp(email, code);
            } catch (error) {
                setSubmitLoading(false);
                updateSubmitPayLabel(activeForm);
                showOtpError(error.message);
                otpInput?.focus();
                return;
            }

            setSubmitLoading(false);
            updateSubmitPayLabel(activeForm);
        }

        if (payBeforeOrder) {
            await startPayAndPlaceOrder();
            return;
        }

        confirmed = true;
        const form = activeForm;
        closeModal();
        form.requestSubmit();
    });

    resendBtn?.addEventListener('click', async function () {
        if (!activeForm || !isGuestCheckout(activeForm) || modalLocked) {
            return;
        }

        clearOtpError();
        if (otpInput) {
            otpInput.value = '';
        }

        try {
            await sendCheckoutOtp(getGuestEmail(activeForm));
        } catch (error) {
            showOtpError(error.message);
        }
    });

    cancelBtn?.addEventListener('click', () => {
        if (canCloseModal()) {
            closeModal();
        }
    });

    backdrop?.addEventListener('click', () => {
        if (canCloseModal()) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open') && canCloseModal()) {
            closeModal();
        }
    });

    document.addEventListener('order-coupon:composition-changed', () => {
        document.querySelectorAll('[data-order-place-confirm], form[action*="order/product"]').forEach((form) => {
            clearPlaceOrderError(form);
        });

        if (activeForm && modal.classList.contains('is-open') && payBeforeOrder) {
            updateSubmitPayLabel(activeForm);
        }
    });
});
