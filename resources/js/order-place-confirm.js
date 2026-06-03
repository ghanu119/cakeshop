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
    const weightRow = document.getElementById('order-confirm-weight-row');
    const flavorRow = document.getElementById('order-confirm-flavor-row');
    const orderTypeRow = document.getElementById('order-confirm-order-type-row');
    const weightValue = document.getElementById('order-confirm-weight');
    const flavorValue = document.getElementById('order-confirm-flavor');
    const orderTypeValue = document.getElementById('order-confirm-order-type');

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

    let activeForm = null;
    let confirmed = false;

    function openModal(form) {
        confirmed = false;
        activeForm = form;

        const hasVariants = form.hasAttribute('data-has-variants');
        const hasFlavors = form.hasAttribute('data-has-flavors');
        const showOrderType = form.hasAttribute('data-show-order-type');

        setRow(weightRow, weightValue, getWeightLabel(form), hasVariants);
        setRow(flavorRow, flavorValue, getFlavorLabel(form), hasFlavors);
        setRow(orderTypeRow, orderTypeValue, getOrderTypeLabel(), showOrderType);

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
        submitBtn?.focus();
    }

    function closeModal() {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

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

    submitBtn?.addEventListener('click', function () {
        if (!activeForm) {
            return;
        }

        confirmed = true;
        const form = activeForm;
        closeModal();
        form.requestSubmit();
    });

    cancelBtn?.addEventListener('click', closeModal);

    backdrop?.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            closeModal();
        }
    });
});
