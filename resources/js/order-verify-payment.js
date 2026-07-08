import { confirmAction } from './admin-swal';

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-verify-payment-form]').forEach((form) => {
        const submitBtn = form.querySelector('button[type="submit"]');
        const submitLabel = form.querySelector('[data-submit-label]');
        const submittingLabel = form.querySelector('[data-submitting-label]');

        function disableSubmitButton() {
            if (!submitBtn) {
                return;
            }

            submitBtn.disabled = true;
            submitLabel?.classList.add('hidden');
            submittingLabel?.classList.remove('hidden');
        }

        form.addEventListener('submit', function (event) {
            if (form.dataset.swalConfirmed === 'true') {
                disableSubmitButton();
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            confirmAction({
                title: form.dataset.confirmTitle || 'Verify payment?',
                text:
                    form.dataset.confirmMessage ||
                    'Are you sure you want to verify payment for this order?',
                confirmText: form.dataset.confirmYes || 'Yes, verify',
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
});
