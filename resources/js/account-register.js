document.addEventListener('DOMContentLoaded', () => {
    const phoneInput = document.querySelector('[data-phone-input]');
    const preview = document.getElementById('phone-claim-preview');
    const form = document.querySelector('[data-register-form]');
    const checkUrl = form?.dataset.checkPhoneUrl;

    if (!phoneInput || !preview || !checkUrl) {
        return;
    }

    let timer = null;

    const checkPhone = () => {
        const phone = phoneInput.value.trim();
        if (phone.length < 8) {
            preview.classList.add('hidden');
            preview.textContent = '';
            return;
        }

        fetch(`${checkUrl}?phone=${encodeURIComponent(phone)}`, {
            headers: { Accept: 'application/json' },
        })
            .then((r) => r.json())
            .then((data) => {
                if (!data.match) {
                    preview.classList.add('hidden');
                    return;
                }

                preview.classList.remove('hidden');
                preview.innerHTML = `<strong>${window.__tFoundStoreAccount || 'We found your store account'}</strong><br>${data.match.name} · ${data.match.phone_masked}`;
            })
            .catch(() => preview.classList.add('hidden'));
    };

    phoneInput.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(checkPhone, 400);
    });
});
