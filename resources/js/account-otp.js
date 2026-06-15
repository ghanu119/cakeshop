document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('[data-otp-input]');
    if (input) {
        input.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
        });
    }
});
