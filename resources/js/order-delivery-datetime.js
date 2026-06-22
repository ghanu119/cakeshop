function clampDeliveryDatetime(input) {
    const min = input.min;
    const max = input.max;

    if (!input.value) {
        return;
    }

    if (min && input.value < min) {
        input.value = min;
    }

    if (max && input.value > max) {
        input.value = max;
    }
}

function bindDeliveryDatetime(input) {
    const min = input.min;
    const minMessage = input.dataset.minMessage || 'Please choose a later time.';

    const validate = () => {
        clampDeliveryDatetime(input);

        if (min && input.value && input.value < min) {
            input.setCustomValidity(minMessage);
        } else {
            input.setCustomValidity('');
        }
    };

    input.addEventListener('change', validate);
    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);

    const form = input.closest('form');
    if (form) {
        form.addEventListener('submit', (event) => {
            validate();

            if (!input.reportValidity()) {
                event.preventDefault();
            }
        });
    }
}

document.querySelectorAll('[data-delivery-datetime]').forEach(bindDeliveryDatetime);
