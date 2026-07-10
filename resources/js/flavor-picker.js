/**
 * Single-select flavor picker for order form.
 */

export const FLAVOR_REQUIRED_MESSAGE = 'Please select a flavor.';

export function validateFlavorSelection(form) {
    if (!form?.hasAttribute('data-has-flavors')) {
        return { ok: true, message: '' };
    }

    const picker = form.querySelector('[data-flavor-picker]');
    if (!picker) {
        return { ok: true, message: '' };
    }

    const hiddenInput = form.querySelector('#flavor_id');
    const value = hiddenInput?.value?.trim() ?? '';

    if (!value) {
        return { ok: false, message: FLAVOR_REQUIRED_MESSAGE };
    }

    return { ok: true, message: '' };
}

export function setFlavorError(form, message) {
    if (!form) {
        return;
    }

    const picker = form.querySelector('[data-flavor-picker]');
    const errorEl = form.querySelector('[data-flavor-error]');
    const summary = form.querySelector('[data-flavor-summary]');

    if (message) {
        picker?.classList.add('flavor-picker--error');

        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.remove('hidden');
            errorEl.setAttribute('role', 'alert');
        }

        summary?.classList.add('is-empty');
    } else {
        picker?.classList.remove('flavor-picker--error');

        if (errorEl) {
            errorEl.textContent = '';
            errorEl.classList.add('hidden');
            errorEl.removeAttribute('role');
        }
    }
}

export function showFlavorRequiredAlert(form, message = FLAVOR_REQUIRED_MESSAGE) {
    setFlavorError(form, message);

    const placeError = form?.querySelector('[data-order-place-error]');
    if (placeError) {
        placeError.textContent = message;
        placeError.classList.remove('hidden');
    }

    scrollToFlavorPicker(form);
}

export function clearFlavorAlert(form) {
    setFlavorError(form, null);

    const placeError = form?.querySelector('[data-order-place-error]');
    if (placeError?.textContent?.trim() === FLAVOR_REQUIRED_MESSAGE) {
        placeError.textContent = '';
        placeError.classList.add('hidden');
    }
}

export function scrollToFlavorPicker(form) {
    const picker = form?.querySelector('[data-flavor-picker]');
    picker?.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function applySummarySelectedStyles(labelTarget) {
    if (!labelTarget) {
        return;
    }

    labelTarget.classList.remove('italic', 'text-rose-400');
    labelTarget.classList.add('font-bold', 'text-rose-900');
}

function applySummaryPlaceholderStyles(labelTarget) {
    if (!labelTarget) {
        return;
    }

    labelTarget.classList.remove('font-bold', 'text-rose-900');
    labelTarget.classList.add('italic', 'text-rose-400');
}

export function initFlavorPicker(root) {
    if (!root) return;

    const form = root.closest('form');
    const hiddenInput = form?.querySelector('#flavor_id') ?? document.getElementById('flavor_id');
    if (!hiddenInput) return;

    const labelTarget = root.dataset.flavorLabelTarget
        ? document.querySelector(root.dataset.flavorLabelTarget)
        : null;
    const placeholderText = labelTarget?.dataset?.flavorPlaceholder?.trim() ?? '';

    const pills = root.querySelectorAll('[data-flavor-id]');

    function selectFlavor(btn) {
        hiddenInput.value = btn.getAttribute('data-flavor-id') || '';

        if (labelTarget) {
            labelTarget.textContent = btn.getAttribute('data-flavor-label') || '';
            applySummarySelectedStyles(labelTarget);
        }

        pills.forEach((pill) => {
            const active = pill === btn;
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (form) {
            clearFlavorAlert(form);
            form.querySelector('[data-flavor-summary]')?.classList.remove('is-empty');
        }
    }

    pills.forEach((pill) => {
        pill.addEventListener('click', () => selectFlavor(pill));
    });

    if (labelTarget && !hiddenInput.value?.trim() && placeholderText) {
        labelTarget.textContent = placeholderText;
        applySummaryPlaceholderStyles(labelTarget);
    }
}

function handleFlavorSubmit(event) {
    const form = event.target;
    if (!form?.hasAttribute('data-has-flavors')) {
        return;
    }

    const result = validateFlavorSelection(form);
    if (!result.ok) {
        event.preventDefault();
        event.stopImmediatePropagation();
        showFlavorRequiredAlert(form, result.message);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flavor-picker]').forEach((root) => initFlavorPicker(root));
    document.querySelectorAll('form[data-has-flavors]').forEach((form) => {
        form.addEventListener('submit', handleFlavorSubmit, true);
    });
});
