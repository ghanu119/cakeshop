import { confirmAction } from './admin-swal';

document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('[data-order-status-form]');
    if (!form) {
        return;
    }

    const select = form.querySelector('[data-order-status-select]');
    const panel = form.querySelector('[data-preparation-panel]');
    const input = form.querySelector('[data-preparation-input]');
    const submitBtn = form.querySelector('[data-order-status-submit]');
    const submitLabel = form.querySelector('[data-submit-label]');
    const submittingLabel = form.querySelector('[data-submitting-label]');

    const allowPreparation = form.hasAttribute('data-allow-preparation');

    function disableSubmitButton() {
        if (!submitBtn) {
            return;
        }

        submitBtn.disabled = true;
        submitLabel?.classList.add('hidden');
        submittingLabel?.classList.remove('hidden');
    }

    function togglePreparationPanel() {
        if (!allowPreparation) {
            return;
        }

        const isProcessing = select.value === 'processing';
        if (!panel || !input) {
            return;
        }

        if (isProcessing) {
            panel.classList.remove('hidden');
            input.removeAttribute('disabled');
            input.setAttribute('required', 'required');
        } else {
            panel.classList.add('hidden');
            input.setAttribute('disabled', 'disabled');
            input.removeAttribute('required');
        }
    }

    select?.addEventListener('change', togglePreparationPanel);
    togglePreparationPanel();

    function formatBalanceAmount(value) {
        const amount = Number.parseFloat(value);

        if (!Number.isFinite(amount)) {
            return '₹0.00';
        }

        return `₹${amount.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })}`;
    }

    function buildOutstandingWarning(form, selectedStatus) {
        const hasOutstandingBalance = form.dataset.hasOutstandingBalance === '1';
        const balanceDue = Number.parseFloat(form.dataset.balanceDue ?? '0');

        if (!hasOutstandingBalance || balanceDue <= 0.01) {
            return null;
        }

        const amount = formatBalanceAmount(balanceDue);

        if (selectedStatus === 'delivered') {
            const template = form.dataset.deliveredUnpaidMessage
                || 'This order still has ₹:amount unpaid. Mark as delivered anyway?';

            return {
                title: form.dataset.deliveredUnpaidTitle || 'Deliver with unpaid balance?',
                text: template.replace(':amount', amount),
                confirmColor: '#d97706',
            };
        }

        if (selectedStatus === 'processing' || selectedStatus === 'completed') {
            const template = form.dataset.outstandingWarningMessage
                || 'This order still has ₹:amount unpaid. Continue updating the status?';

            return {
                title: form.dataset.outstandingWarningTitle || 'Outstanding balance',
                text: template.replace(':amount', amount),
                confirmColor: '#d97706',
            };
        }

        return null;
    }

    form.addEventListener('submit', function (event) {
        if (form.dataset.statusConfirmed === 'true') {
            disableSubmitButton();
            return;
        }

        const initialStatus = form.dataset.initialOrderStatus;
        const selectedStatus = select?.value;

        if (initialStatus && selectedStatus && selectedStatus !== initialStatus) {
            event.preventDefault();
            event.stopImmediatePropagation();

            const selectedLabel = select.options[select.selectedIndex]?.text?.trim() || selectedStatus;
            const template =
                form.dataset.statusConfirmMessage ||
                'Are you sure you want to change the order status to :status?';
            const message = template.replace(':status', selectedLabel);
            const outstandingWarning = buildOutstandingWarning(form, selectedStatus);

            const runStatusConfirm = () => confirmAction({
                title: form.dataset.statusConfirmTitle || 'Change order status?',
                text: message,
                confirmText: form.dataset.statusConfirmYes || 'Yes, update',
                cancelText: form.dataset.statusConfirmNo || 'Cancel',
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                form.dataset.statusConfirmed = 'true';
                disableSubmitButton();
                form.requestSubmit();
            });

            if (outstandingWarning) {
                confirmAction({
                    title: outstandingWarning.title,
                    text: outstandingWarning.text,
                    confirmText: form.dataset.statusConfirmYes || 'Yes, update',
                    cancelText: form.dataset.statusConfirmNo || 'Cancel',
                    confirmColor: outstandingWarning.confirmColor,
                }).then((result) => {
                    if (!result.isConfirmed) {
                        return;
                    }

                    runStatusConfirm();
                });

                return;
            }

            runStatusConfirm();

            return;
        }

        disableSubmitButton();
    });
});
