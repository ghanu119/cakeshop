import { confirmAction } from './admin-swal';

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-record-cash-payment-form]');
    if (!form) {
        return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    const submitLabel = form.querySelector('[data-submit-label]');
    const submittingLabel = form.querySelector('[data-submitting-label]');
    const amountInput = form.querySelector('[name="amount_received"]');

    function disableSubmitButton() {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = true;
        submitLabel?.classList.add('hidden');
        submittingLabel?.classList.remove('hidden');
    }

    function formatAmount(value) {
        const amount = Number.parseFloat(value);

        if (!Number.isFinite(amount)) {
            return '₹0.00';
        }

        return `₹${amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    form.addEventListener('submit', function (event) {
        if (form.dataset.swalConfirmed === 'true') {
            disableSubmitButton();
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const amount = amountInput?.value ?? '0';
        const template = form.dataset.confirmMessage || 'Record :amount received for this order?';
        const message = template.replace(':amount', formatAmount(amount));

        confirmAction({
            title: form.dataset.confirmTitle || 'Record cash payment?',
            text: message,
            confirmText: form.dataset.confirmYes || 'Yes, record payment',
            cancelText: form.dataset.confirmNo || 'Cancel',
            confirmColor: '#d97706',
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.swalConfirmed = 'true';
            disableSubmitButton();
            form.requestSubmit();
        });
    });
});
