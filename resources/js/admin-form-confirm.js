import { confirmAction, confirmDelete } from './admin-swal';

document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener(
        'submit',
        function (event) {
        const form = event.target.closest('form[data-swal-confirm]');
        if (!form) {
            return;
        }

        if (form.dataset.swalConfirmed === 'true') {
            return;
        }

        event.preventDefault();
        event.stopImmediatePropagation();

        const title = form.dataset.swalConfirmTitle || 'Are you sure?';
        const text = form.dataset.swalConfirmText || undefined;
        const confirmText = form.dataset.swalConfirmYes;
        const cancelText = form.dataset.swalConfirmNo;
        const confirmColor = form.dataset.swalConfirmColor;
        const isDanger = form.dataset.swalConfirmVariant === 'danger';

        const options = {
            title,
            text,
            ...(confirmText ? { confirmText } : {}),
            ...(cancelText ? { cancelText } : {}),
            ...(confirmColor ? { confirmColor } : {}),
        };

        const confirmFn = isDanger ? confirmDelete : confirmAction;

        confirmFn(options).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            form.dataset.swalConfirmed = 'true';
            form.requestSubmit();
        });
        },
        true,
    );
});
