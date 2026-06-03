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

    form.addEventListener('submit', function () {
        if (!submitBtn) {
            return;
        }
        submitBtn.disabled = true;
        submitLabel?.classList.add('hidden');
        submittingLabel?.classList.remove('hidden');
    });
});
