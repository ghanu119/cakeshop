import {
    extractApiValidationError,
    validateCouponsForCheckout,
} from './order-checkout-validate';
import { getCsrfToken, refreshCsrfToken, applyCsrfToken } from './csrf-token';
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
    const cashSection = document.querySelector('[data-in-store-cash-section]');
    const cashInput = document.querySelector('[data-in-store-cash-input]');
    const cashError = document.querySelector('[data-in-store-cash-error]');
    const cashDisplay = document.querySelector('[data-in-store-cash-display]');
    const balanceDueDisplay = document.querySelector('[data-in-store-balance-due]');
    const orderTotalDisplay = document.querySelector('[data-in-store-order-total]');
    const channelButtons = modal.querySelectorAll('[data-otp-channel]');
    const sendOtpUrl = modal.dataset.sendOtpUrl;
    const verifyOtpUrl = modal.dataset.verifyOtpUrl;
    const otpRequiredMessage = modal.dataset.otpRequiredMessage ?? 'Please enter the 6-digit verification code.';
    const otpSendingMessage = modal.dataset.otpSendingMessage ?? 'Sending verification code...';
    const verifyingLabel = modal.dataset.otpVerifyingLabel ?? 'Verifying...';
    const payBeforeOrder = modal.dataset.payBeforeOrder === '1';
    const isImpersonating = modal.dataset.isImpersonating === '1';
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

    function parseAmountFromText(text) {
        if (!text) {
            return null;
        }

        const normalized = String(text).replace(/[^\d.,-]/g, '').replace(/,/g, '');
        const value = Number.parseFloat(normalized);

        return Number.isFinite(value) ? value : null;
    }

    function formatCurrency(amount) {
        return `${currencySymbol}${Number(amount).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function getEstimatedTotalText(form) {
        const sources = [
            () => form?.dataset.checkoutTotal,
            () => form?.querySelector('[data-order-summary] [data-summary-total]')?.textContent?.trim(),
            () => form?.querySelector('[data-summary-total]')?.textContent?.trim(),
            () => form?.querySelector('#order-estimated-total')?.textContent?.trim(),
            () => document.getElementById('order-estimated-total')?.textContent?.trim(),
        ];

        for (const read of sources) {
            const text = read();

            if (text && parseAmountFromText(text) !== null) {
                return text;
            }
        }

        return null;
    }

    function getOrderTotalAmount(form) {
        if (checkoutQuoteTotal !== null && Number.isFinite(checkoutQuoteTotal)) {
            return checkoutQuoteTotal;
        }

        const parsed = parseAmountFromText(getEstimatedTotalText(form));

        if (parsed !== null) {
            return parsed;
        }

        return computeFallbackOrderTotal(form);
    }

    function computeFallbackOrderTotal(form) {
        if (!form) {
            return null;
        }

        const unitPriceText = form.querySelector('#order-unit-price')?.textContent?.trim()
            ?? document.getElementById('order-unit-price')?.textContent?.trim();
        const unitPrice = parseAmountFromText(unitPriceText);
        const quantity = Math.max(1, Number.parseInt(form.querySelector('#quantity')?.value ?? '1', 10) || 1);

        if (unitPrice === null) {
            return null;
        }

        return unitPrice * quantity;
    }

    function getCashReceivedValue() {
        const raw = String(cashInput?.value ?? '0').trim().replace(/,/g, '');
        const value = Number.parseFloat(raw);

        return Number.isFinite(value) ? Math.max(0, value) : 0;
    }

    function syncCashReceivedToForm(form) {
        const hidden = form?.querySelector('#cash_received, [name="cash_received"]');

        if (hidden) {
            hidden.value = String(getCashReceivedValue());
        }
    }

    function clearCashError() {
        if (!cashError) {
            return;
        }

        cashError.textContent = '';
        cashError.classList.add('hidden');
    }

    function showCashError(message) {
        if (!cashError) {
            return;
        }

        cashError.textContent = message;
        cashError.classList.remove('hidden');
    }

    function updateInStoreCashSummary(form) {
        if (!isImpersonating || !cashSection) {
            return;
        }

        const total = getOrderTotalAmount(form);
        const cash = getCashReceivedValue();
        const balance = total !== null ? Math.max(0, total - cash) : null;

        if (orderTotalDisplay) {
            orderTotalDisplay.textContent = total !== null ? formatCurrency(total) : '—';
        }

        if (cashDisplay) {
            cashDisplay.textContent = formatCurrency(cash);
        }

        if (balanceDueDisplay) {
            if (balance === null) {
                balanceDueDisplay.textContent = '—';
            } else if (balance <= 0.01) {
                balanceDueDisplay.textContent = formatCurrency(0);
                balanceDueDisplay.classList.remove('text-amber-700');
                balanceDueDisplay.classList.add('text-green-700');
            } else {
                balanceDueDisplay.textContent = formatCurrency(balance);
                balanceDueDisplay.classList.remove('text-green-700');
                balanceDueDisplay.classList.add('text-amber-700');
            }
        }

        syncCashReceivedToForm(form);
    }

    function validateInStoreCash(form) {
        if (!isImpersonating) {
            return true;
        }

        clearCashError();

        const total = getOrderTotalAmount(form);
        const cash = getCashReceivedValue();

        if (total === null) {
            showCashError('Unable to read the order total. Please try again.');
            cashInput?.focus();
            return false;
        }

        if (cash > total + 0.01) {
            showCashError(`Cash received cannot exceed the order total of ${formatCurrency(total)}.`);
            cashInput?.focus();
            return false;
        }

        syncCashReceivedToForm(form);

        return true;
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

            checkoutQuoteTotal = typeof couponResult.total === 'number'
                ? couponResult.total
                : null;

            if (checkoutQuoteTotal !== null) {
                form.dataset.checkoutTotal = String(checkoutQuoteTotal);
            } else {
                delete form.dataset.checkoutTotal;
            }

            openModal(form);
        } catch (error) {
            showPlaceOrderError(form, error.message || 'Something went wrong. Please try again.');
            scrollToPlaceOrderError(form);
        } finally {
            setPlaceOrderButtonLoading(form, false);
        }
    }
    const whatsappEnabled = modal.dataset.whatsappEnabled === '1';
    const otpStatusEmail = modal.dataset.otpStatusEmail ?? 'Enter the verification code we sent to your email.';
    const otpStatusWhatsapp = modal.dataset.otpStatusWhatsapp ?? 'Enter the verification code we sent to your WhatsApp.';
    const otpMissingEmail = modal.dataset.otpMissingEmail ?? 'Please add an email address to verify by email.';
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    let otpChannel = 'email';

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

    function getGuestContact(form) {
        const formData = new FormData(form);

        const readField = (name) => {
            const fromForm = formData.get(name);
            if (typeof fromForm === 'string' && fromForm.trim() !== '') {
                return fromForm.trim();
            }

            return form.querySelector(`[name="${name}"]`)?.value?.trim()
                ?? form.querySelector(`#${name}`)?.value?.trim()
                ?? '';
        };

        return {
            guest_name: readField('guest_name'),
            guest_email: readField('guest_email'),
            guest_phone: readField('guest_phone'),
        };
    }

    function getGuestEmail(form) {
        return getGuestContact(form).guest_email;
    }

    function hasGuestEmail(form) {
        return Boolean(getGuestEmail(form));
    }

    function getGuestPhone(form) {
        return getGuestContact(form).guest_phone;
    }

    function markCheckoutAuthenticated(form) {
        form.removeAttribute('data-guest-checkout');

        if (otpSection) {
            otpSection.classList.add('hidden');
        }

        clearOtpError();

        if (otpInput) {
            otpInput.value = '';
        }
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

        if (modalLocked || modal.classList.contains('is-processing')) {
            submitBtn.disabled = loading;
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

    function updateChannelToggleVisibility(form) {
        const emailBtn = modal.querySelector('[data-otp-channel="email"]');
        if (!emailBtn) {
            return;
        }

        if (hasGuestEmail(form)) {
            emailBtn.classList.remove('hidden');
            emailBtn.disabled = false;
        } else {
            emailBtn.classList.add('hidden');
            if (otpChannel === 'email') {
                setChannel(whatsappEnabled && getGuestPhone(form) ? 'whatsapp' : 'email');
            }
        }
    }

    function updateChannelButtons() {
        channelButtons.forEach((btn) => {
            const active = btn.dataset.otpChannel === otpChannel;
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');
            btn.classList.toggle('bg-white', active);
            btn.classList.toggle('shadow', active);
            btn.classList.toggle('text-stone-900', active);
            btn.classList.toggle('text-stone-500', !active);
        });
    }

    function setChannel(channel) {
        otpChannel = whatsappEnabled && channel === 'whatsapp' ? 'whatsapp' : 'email';
        updateChannelButtons();
        if (otpStatus) {
            otpStatus.textContent = otpChannel === 'whatsapp' ? otpStatusWhatsapp : otpStatusEmail;
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
            const nestedErrors = data.data?.errors ?? data.errors;
            const message = (nestedErrors ? Object.values(nestedErrors).flat()[0] : null)
                || data.message
                || 'Request failed';
            const error = new Error(message);
            error.data = data;
            throw error;
        }

        return data;
    }

    async function sendCheckoutOtp(form) {
        const email = getGuestEmail(form);
        const payload = otpChannel === 'whatsapp'
            ? {
                channel: 'whatsapp',
                phone: getGuestPhone(form),
                ...(email ? { email } : {}),
            }
            : { channel: 'email', email };

        const data = await postJsonLocal(sendOtpUrl, payload);

        if (otpStatus && data.message) {
            otpStatus.textContent = data.message;
        }

        return data;
    }

    async function verifyCheckoutOtp(form, code) {
        if (otpChannel === 'whatsapp') {
            await postJsonLocal(verifyOtpUrl, {
                channel: 'whatsapp',
                phone: getGuestPhone(form),
                code,
            });

            return;
        }

        const contact = getGuestContact(form);

        if (! contact.guest_name || ! contact.guest_phone) {
            throw new Error(messageFor(messages, 'contact_required', 'Please fill in your name and phone before verifying.'));
        }

        const data = await postJsonLocal(verifyOtpUrl, {
            channel: 'email',
            email: getGuestEmail(form),
            code,
            guest_name: contact.guest_name,
            guest_phone: contact.guest_phone,
        });

        if (data.csrf_token) {
            applyCsrfToken(data.csrf_token);
        } else {
            refreshCsrfToken();
        }

        return data;
    }

    let activeForm = null;
    let confirmed = false;
    let otpSending = false;
    let checkoutPayload = null;
    let modalLocked = false;
    let checkoutQuoteTotal = null;

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
        formData.set('_token', getCsrfToken());
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

    async function requestOtp(form) {
        if (!otpSection || !otpInput) {
            return;
        }

        otpSection.classList.remove('hidden');
        otpInput.value = '';
        clearOtpError();

        if (otpChannel === 'email' && !getGuestEmail(form)) {
            showOtpError(otpMissingEmail);
            return;
        }

        if (otpChannel === 'whatsapp' && !getGuestPhone(form)) {
            return;
        }

        if (otpSending) {
            return;
        }

        otpSending = true;
        if (otpStatus) {
            otpStatus.textContent = otpSendingMessage;
        }

        let fallbackToEmail = false;

        try {
            const data = await sendCheckoutOtp(form);
            if (otpStatus && data?.message) {
                otpStatus.textContent = data.message;
            }
        } catch (error) {
            showOtpError(error.message);
            if (whatsappEnabled && otpChannel === 'whatsapp' && error?.data?.fallback === 'email' && hasGuestEmail(form)) {
                fallbackToEmail = true;
            }
        } finally {
            otpSending = false;
        }

        if (fallbackToEmail) {
            setChannel('email');
            if (getGuestEmail(form)) {
                await requestOtp(form);
            }
        }
    }

    async function prepareGuestOtp(form) {
        if (!otpSection || !otpInput) {
            return;
        }

        const defaultChannel = whatsappEnabled && getGuestPhone(form)
            ? 'whatsapp'
            : (hasGuestEmail(form) ? 'email' : 'whatsapp');
        setChannel(defaultChannel);
        updateChannelToggleVisibility(form);
        await requestOtp(form);
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
                updateChannelToggleVisibility(form);
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
        updateInStoreCashSummary(form);

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

    channelButtons.forEach((btn) => {
        btn.addEventListener('click', async function () {
            if (!activeForm) {
                return;
            }

            const channel = btn.dataset.otpChannel;
            if (channel === otpChannel) {
                return;
            }

            if (channel === 'email' && !hasGuestEmail(activeForm)) {
                showOtpError(otpMissingEmail);
                return;
            }

            setChannel(channel);
            await requestOtp(activeForm);
        });
    });

    document.addEventListener('input', (event) => {
        if (!activeForm || event.target?.id !== 'guest_email') {
            return;
        }

        updateChannelToggleVisibility(activeForm);
    });

    submitBtn?.addEventListener('click', async function () {
        if (!activeForm || modalLocked) {
            return;
        }

        clearOtpError();
        clearReviewError();

        if (isGuestCheckout(activeForm)) {
            const code = otpInput?.value?.trim() ?? '';

            if (code.length !== 6) {
                showOtpError(otpRequiredMessage);
                otpInput?.focus();
                return;
            }

            setSubmitLoading(true);

            try {
                await verifyCheckoutOtp(activeForm, code);
                markCheckoutAuthenticated(activeForm);
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

        if (!validateInStoreCash(activeForm)) {
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

        await requestOtp(activeForm);
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

    cashInput?.addEventListener('input', () => {
        if (cashInput) {
            cashInput.value = cashInput.value.replace(/[^\d.]/g, '');
        }

        if (activeForm) {
            updateInStoreCashSummary(activeForm);
            clearCashError();
        }
    });

    document.addEventListener('order-coupon:composition-changed', () => {
        document.querySelectorAll('[data-order-place-confirm], form[action*="order/product"]').forEach((form) => {
            clearPlaceOrderError(form);
        });

        if (activeForm && modal.classList.contains('is-open') && payBeforeOrder) {
            updateSubmitPayLabel(activeForm);
        }

        if (activeForm && modal.classList.contains('is-open') && isImpersonating) {
            checkoutQuoteTotal = null;
            delete activeForm.dataset.checkoutTotal;
            updateInStoreCashSummary(activeForm);
        }
    });
});
