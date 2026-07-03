import { adminApi } from './admin-api';
import { showAdminToast } from './admin-toast';

document.addEventListener('DOMContentLoaded', () => {
    const pusherButton = document.querySelector('[data-test-pusher]');
    if (pusherButton) {
        pusherButton.addEventListener('click', async () => {
            const url = pusherButton.getAttribute('data-test-pusher-url');
            if (!url) return;

            pusherButton.disabled = true;
            const result = await adminApi('post', url);
            pusherButton.disabled = false;

            showAdminToast(result.message, { variant: result.ok ? 'success' : 'error' });
        });
    }

    const razorpayButton = document.querySelector('[data-test-razorpay]');
    if (razorpayButton) {
        razorpayButton.addEventListener('click', async () => {
            const url = razorpayButton.getAttribute('data-test-razorpay-url');
            if (!url) return;

            razorpayButton.disabled = true;
            const result = await adminApi('post', url);
            razorpayButton.disabled = false;

            showAdminToast(result.message, { variant: result.ok ? 'success' : 'error' });
        });
    }
});
