import Swal from 'sweetalert2';

document.addEventListener('DOMContentLoaded', () => {
    const el = document.querySelector('[data-admin-flash]');
    if (!el) {
        return;
    }

    const success = el.dataset.success;
    const error = el.dataset.error;

    if (success) {
        Swal.fire({
            icon: 'success',
            title: success,
            timer: 2500,
            showConfirmButton: false,
        });
    } else if (error) {
        Swal.fire({
            icon: 'error',
            title: error,
        });
    }
});
