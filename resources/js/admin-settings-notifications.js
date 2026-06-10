import { adminApi } from './admin-api';
import { showAdminToast } from './admin-toast';

document.addEventListener('DOMContentLoaded', () => {
    const button = document.querySelector('[data-test-pusher]');
    if (!button) return;

    button.addEventListener('click', async () => {
        const url = button.getAttribute('data-test-pusher-url');
        if (!url) return;

        button.disabled = true;
        const result = await adminApi('post', url);
        button.disabled = false;

        showAdminToast(result.message, { variant: result.ok ? 'success' : 'error' });
    });
});
