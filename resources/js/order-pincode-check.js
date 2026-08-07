import $ from 'jquery';

document.addEventListener('DOMContentLoaded', function () {
    const panel =
        document.querySelector('[data-delivery-details-panel]') ||
        document.querySelector('[data-delivery-address-panel]');
    const pincodeInput = document.querySelector('[data-delivery-pincode-input]');
    const statusWrap = document.querySelector('[data-pincode-status-wrap]');
    const statusBox = document.querySelector('[data-pincode-status-box]');
    const statusIcon = document.querySelector('[data-pincode-status-icon]');
    const statusEl = document.querySelector('[data-pincode-status]');
    const spinner = document.querySelector('[data-pincode-spinner]');
    const switchToTakeaway = document.querySelector('[data-switch-to-takeaway]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!panel || !pincodeInput) {
        return;
    }

    const checkUrl = panel.dataset.pincodeCheckUrl;
    let activeController = null;
    let pincodeValid = false;

    const icons = {
        success:
            '<svg class="h-4 w-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>',
        error:
            '<svg class="h-4 w-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>',
        muted:
            '<svg class="h-4 w-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    };

    function getSubmitButtons() {
        return document.querySelectorAll('[data-order-place-confirm] button[type="submit"]');
    }

    function setSubmitEnabled(enabled) {
        getSubmitButtons().forEach((button) => {
            button.disabled = !enabled;
        });
        document.querySelectorAll('[data-order-place-confirm]').forEach((form) => {
            form.dataset.pincodeValid = enabled ? 'true' : 'false';
        });
    }

    function showSpinner(show) {
        if (!spinner) {
            return;
        }

        spinner.classList.toggle('hidden', !show);
        spinner.classList.toggle('flex', show);
    }

    function setStatus(message, tone) {
        if (!statusWrap || !statusBox || !statusEl) {
            return;
        }

        if (!message) {
            statusWrap.classList.add('hidden');
            statusEl.textContent = '';
            if (statusIcon) {
                statusIcon.innerHTML = '';
            }
            return;
        }

        statusWrap.classList.remove('hidden');
        statusEl.textContent = message;

        statusBox.className =
            'flex max-w-md items-start gap-2 rounded-xl border px-3 py-2.5 text-sm font-medium';

        if (tone === 'success') {
            statusBox.classList.add('border-emerald-200', 'bg-emerald-50', 'text-emerald-800');
            if (statusIcon) {
                statusIcon.innerHTML = icons.success;
            }
        } else if (tone === 'error') {
            statusBox.classList.add('border-red-200', 'bg-red-50', 'text-red-700');
            if (statusIcon) {
                statusIcon.innerHTML = icons.error;
            }
        } else {
            statusBox.classList.add('border-stone-200', 'bg-stone-50', 'text-stone-600');
            if (statusIcon) {
                statusIcon.innerHTML = icons.muted;
            }
        }
    }

    function setInputState(state) {
        // Select2 hides the native <select> and renders its own box, so the
        // success/error look is applied to the wrapping .pincode-select element
        // (styled in _picker-styles.blade.php) rather than the select itself.
        const wrap = pincodeInput.closest('.pincode-select');
        wrap?.classList.remove('is-success', 'is-error');
        pincodeInput.removeAttribute('aria-invalid');

        if (state === 'success') {
            wrap?.classList.add('is-success');
        } else if (state === 'error') {
            wrap?.classList.add('is-error');
            pincodeInput.setAttribute('aria-invalid', 'true');
        }
    }

    // Address enable/disable is driven solely by the fulfillment-type toggle
    // (order-fulfillment-type.js) — pincode serviceability only gates submission
    // and shows a confirmation/error message, it no longer locks the address field.
    function resetPincodeState() {
        pincodeValid = false;
        if (activeController) {
            activeController.abort();
            activeController = null;
        }

        showSpinner(false);
        setInputState('idle');
        setStatus('', 'idle');
        if (switchToTakeaway) {
            switchToTakeaway.classList.add('hidden');
        }

        const hiddenInput = document.getElementById('fulfillment_type');
        const isDelivery = hiddenInput?.value === 'delivery';
        setSubmitEnabled(!isDelivery);
    }

    function markPincodeValid() {
        pincodeValid = true;
        if (switchToTakeaway) {
            switchToTakeaway.classList.add('hidden');
        }
        setSubmitEnabled(true);
    }

    function markPincodeInvalid(message) {
        pincodeValid = false;
        setStatus(message, 'error');
        setInputState('error');
        if (switchToTakeaway) {
            switchToTakeaway.classList.remove('hidden');
        }
        setSubmitEnabled(false);
    }

    async function checkPincode(digits) {
        if (digits.length !== 6) {
            resetPincodeState();
            return;
        }

        if (!checkUrl || !csrfToken) {
            return;
        }

        showSpinner(true);
        setStatus('Checking pincode…', 'muted');
        setInputState('idle');

        if (activeController) {
            activeController.abort();
        }

        activeController = new AbortController();

        try {
            const response = await fetch(checkUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ pincode: digits }),
                signal: activeController.signal,
            });

            const data = await response.json();
            showSpinner(false);

            if (data.serviceable) {
                setStatus(data.message || 'Delivery available.', 'success');
                setInputState('success');
                markPincodeValid();
            } else {
                markPincodeInvalid(data.message || 'Sorry, we do not deliver to this pincode yet.');
            }
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            showSpinner(false);
            markPincodeInvalid('Could not verify pincode. Please try again.');
        } finally {
            activeController = null;
        }
    }

    // Select2 dispatches its "change" event through jQuery, not the native DOM
    // event system, so a plain addEventListener('change', ...) here never fires
    // when a pincode is picked from the dropdown — bind via jQuery instead.
    $(pincodeInput).on('change', function () {
        const digits = pincodeInput.value.replace(/\D/g, '');
        checkPincode(digits);
    });

    document.addEventListener('fulfillment:delivery-selected', function () {
        resetPincodeState();
        const digits = pincodeInput.value.replace(/\D/g, '');
        if (digits.length === 6) {
            checkPincode(digits);
        } else {
            setSubmitEnabled(false);
        }
    });

    document.addEventListener('fulfillment:takeaway-selected', resetPincodeState);

    document.querySelectorAll('[data-order-place-confirm]').forEach((form) => {
        form.addEventListener(
            'submit',
            function (event) {
                const hiddenInput = document.getElementById('fulfillment_type');
                if (hiddenInput?.value === 'delivery' && !pincodeValid) {
                    event.preventDefault();
                    event.stopImmediatePropagation();
                    markPincodeInvalid('Please select a serviceable delivery pincode.');
                    pincodeInput.focus();
                }
            },
            true
        );
    });

    const hiddenInput = document.getElementById('fulfillment_type');
    if (hiddenInput?.value === 'delivery') {
        setSubmitEnabled(false);
        const digits = pincodeInput.value.replace(/\D/g, '');
        if (digits.length === 6) {
            checkPincode(digits);
        }
    }
});
