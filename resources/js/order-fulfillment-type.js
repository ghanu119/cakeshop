document.addEventListener('DOMContentLoaded', function () {
    const picker = document.querySelector('[data-fulfillment-picker]');
    const takeawayPanel = document.querySelector('[data-takeaway-notice-panel]');
    const addressInput = document.querySelector('[data-delivery-address-input]');
    const pincodeInput = document.querySelector('[data-delivery-pincode-input]');
    const hiddenInput = document.getElementById('fulfillment_type');

    if (!picker) {
        return;
    }

    const pills = picker.querySelectorAll('[data-fulfillment-type]');

    function deliveryPanels() {
        const panels = document.querySelectorAll('[data-delivery-panel]');

        if (panels.length > 0) {
            return panels;
        }

        return document.querySelectorAll('[data-delivery-address-panel], [data-delivery-address-column]');
    }

    function syncPanels() {
        const selected = picker.querySelector('[data-fulfillment-type][aria-pressed="true"]');
        const isDelivery = selected?.dataset.fulfillmentType === 'delivery';

        deliveryPanels().forEach((panel) => {
            if (isDelivery) {
                panel.classList.remove('hidden');
            } else {
                panel.classList.add('hidden');
            }
        });

        if (takeawayPanel) {
            if (isDelivery) {
                takeawayPanel.classList.add('hidden');
            } else {
                takeawayPanel.classList.remove('hidden');
            }
        }

        if (isDelivery) {
            pincodeInput?.removeAttribute('disabled');
            pincodeInput?.setAttribute('required', 'required');
            addressInput?.removeAttribute('disabled');
            addressInput?.setAttribute('required', 'required');
            document.dispatchEvent(new CustomEvent('fulfillment:delivery-selected'));
        } else {
            pincodeInput?.setAttribute('disabled', 'disabled');
            pincodeInput?.removeAttribute('required');
            addressInput?.removeAttribute('required');
            addressInput?.setAttribute('disabled', 'disabled');
            document.dispatchEvent(new CustomEvent('fulfillment:takeaway-selected'));
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

        syncPanels();
    }

    pills.forEach((pill) => {
        pill.addEventListener('click', () => selectFulfillment(pill));
    });

    document.querySelector('[data-switch-to-takeaway]')?.addEventListener('click', () => {
        const takeawayPill = picker.querySelector('[data-fulfillment-type="takeaway"]');
        if (takeawayPill) {
            selectFulfillment(takeawayPill);
        }
    });

    syncPanels();
});
