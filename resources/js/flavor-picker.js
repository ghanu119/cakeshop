/**
 * Single-select flavor picker for order form.
 */
export function initFlavorPicker(root) {
    if (!root) return;

    const hiddenInput = document.getElementById('flavor_id');
    if (!hiddenInput) return;

    const labelTarget = root.dataset.flavorLabelTarget
        ? document.querySelector(root.dataset.flavorLabelTarget)
        : null;

    const pills = root.querySelectorAll('[data-flavor-id]');

    function selectFlavor(btn) {
        hiddenInput.value = btn.getAttribute('data-flavor-id') || '';

        if (labelTarget) {
            labelTarget.textContent = btn.getAttribute('data-flavor-label') || '';
        }

        pills.forEach((pill) => {
            const active = pill === btn;
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });
    }

    pills.forEach((pill) => {
        pill.addEventListener('click', () => selectFlavor(pill));
    });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-flavor-picker]').forEach((root) => initFlavorPicker(root));
});
