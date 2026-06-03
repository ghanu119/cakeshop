document.addEventListener('DOMContentLoaded', function () {
    const picker = document.querySelector('[data-fulfillment-picker]');
    const panel = document.querySelector('[data-delivery-address-panel]');
    const addressInput = document.querySelector('[data-delivery-address-input]');
    const hiddenInput = document.getElementById('fulfillment_type');

    if (!picker) {
        return;
    }

    const pills = picker.querySelectorAll('[data-fulfillment-type]');

    function syncAddressPanel() {
        if (!panel) {
            return;
        }

        const selected = picker.querySelector('[data-fulfillment-type][aria-pressed="true"]');
        const isDelivery = selected?.dataset.fulfillmentType === 'delivery';

        if (isDelivery) {
            panel.classList.remove('hidden');
            addressInput?.setAttribute('required', 'required');
            addressInput?.removeAttribute('disabled');
        } else {
            panel.classList.add('hidden');
            addressInput?.removeAttribute('required');
            addressInput?.setAttribute('disabled', 'disabled');
        }
    }

    function selectFulfillment(button) {
        if (hiddenInput) {
            hiddenInput.value = button.dataset.fulfillmentType || 'takeaway';
        }

        pills.forEach((pill) => {
            const active = pill === button;
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        syncAddressPanel();
    }

    pills.forEach((pill) => {
        pill.addEventListener('click', () => selectFulfillment(pill));
    });

    syncAddressPanel();
});
