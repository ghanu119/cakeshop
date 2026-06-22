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
    const resendBtn = modal.querySelector('[data-order-confirm-resend-otp]');
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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

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

    function setSubmitLoading(loading) {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = loading;
        submitBtn.dataset.originalLabel ??= submitBtn.textContent;
        submitBtn.textContent = loading
            ? verifyingLabel
            : submitBtn.dataset.originalLabel;
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        });

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
        const data = await postJson(sendOtpUrl, { email });
        if (otpStatus && data.message) {
            otpStatus.textContent = data.message;
        }
    }

    async function verifyCheckoutOtp(email, code) {
        await postJson(verifyOtpUrl, { email, code });
    }

    let activeForm = null;
    let confirmed = false;
    let otpSending = false;

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
        activeForm = form;

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

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        (guest && otpInput ? otpInput : submitBtn)?.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
        setSubmitLoading(false);
        clearOtpError();

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
                openModal(form);
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
                openModal(form);
            },
            true
        );
    });

    submitBtn?.addEventListener('click', async function () {
        if (!activeForm) {
            return;
        }

        clearOtpError();

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
                showOtpError(error.message);
                otpInput?.focus();
                return;
            }
        }

        confirmed = true;
        const form = activeForm;
        closeModal();
        form.requestSubmit();
    });

    resendBtn?.addEventListener('click', async function () {
        if (!activeForm || !isGuestCheckout(activeForm)) {
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

    cancelBtn?.addEventListener('click', closeModal);

    backdrop?.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
