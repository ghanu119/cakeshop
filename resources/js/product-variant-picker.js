/**
 * Weight / variant picker: syncs unit price, total, hidden product_variant_id, and optional order link.
 */
export function initProductVariantPicker(root) {
    if (!root) return;

    const choices = JSON.parse(root.dataset.choices || '[]');
    if (!choices.length) return;

    const unitPriceEl = document.querySelector(root.dataset.unitPriceTarget);
    const totalEl = document.querySelector(root.dataset.totalTarget);
    const hiddenInput = document.querySelector(root.dataset.hiddenInputTarget);
    const quantityInput = document.querySelector(root.dataset.quantityTarget);
    const orderLink = document.querySelector(root.dataset.orderLinkTarget);
    const startingLabel = document.querySelector(root.dataset.startingLabelTarget);
    const weightLabelEl = document.querySelector(root.dataset.weightLabelTarget);
    const pills = root.querySelectorAll('[data-variant-id]');

    const symbol = root.dataset.currencySymbol || '₹';

    function formatMoney(amount) {
        return symbol + Number(amount).toFixed(2);
    }

    function getQuantity() {
        const q = quantityInput ? parseInt(quantityInput.value, 10) : 1;
        return Number.isFinite(q) && q > 0 ? q : 1;
    }

    function selectVariant(id) {
        const choice = choices.find((c) => c.id === id);
        if (!choice) return;

        pills.forEach((pill) => {
            const active = parseInt(pill.dataset.variantId, 10) === id;
            pill.classList.toggle('ring-2', active);
            pill.classList.toggle('ring-amber-500', active);
            pill.classList.toggle('bg-amber-100', active);
            pill.setAttribute('aria-pressed', active ? 'true' : 'false');
        });

        if (unitPriceEl) unitPriceEl.textContent = formatMoney(choice.price);
        if (totalEl) totalEl.textContent = formatMoney(choice.price * getQuantity());
        if (weightLabelEl) weightLabelEl.textContent = choice.label;
        if (hiddenInput) hiddenInput.value = id;
        if (startingLabel) startingLabel.classList.add('hidden');
        if (orderLink && orderLink.dataset.baseUrl) {
            const url = new URL(orderLink.dataset.baseUrl, window.location.origin);
            url.searchParams.set('product_variant_id', id);
            orderLink.href = url.pathname + url.search;
        }
    }

    pills.forEach((pill) => {
        pill.addEventListener('click', () => selectVariant(parseInt(pill.dataset.variantId, 10)));
    });

    if (quantityInput) {
        quantityInput.addEventListener('input', () => {
            const id = hiddenInput ? parseInt(hiddenInput.value, 10) : choices[0]?.id;
            if (id) selectVariant(id);
        });
    }

    const initialId = parseInt(root.dataset.initialVariantId || choices.find((c) => c.is_default)?.id || choices[0]?.id, 10);
    if (initialId) selectVariant(initialId);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-variant-picker]').forEach((root) => initProductVariantPicker(root));
});
