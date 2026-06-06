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

            confirmAction({
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

            return;
        }

        disableSubmitButton();
    });
});
