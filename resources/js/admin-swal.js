import Swal from 'sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';

export function confirmAction({
    title,
    text,
    confirmText = 'Yes',
    cancelText = 'Cancel',
    confirmColor = '#4f46e5',
}) {
    return Swal.fire({
        title,
        text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: confirmColor,
        cancelButtonColor: '#6b7280',
        confirmButtonText: confirmText,
        cancelButtonText: cancelText,
        reverseButtons: true,
    });
}
