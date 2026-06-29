/**
 * Weight / variant picker: syncs unit price, total, hidden product_variant_id, and optional order link.
 */
export function initProductVariantPicker(root) {
    if (!root) return;

    const choices = JSON.parse(root.dataset.choices || '[]');
    if (!choices.length) return;

    function sortChoicesByGrams(list) {
        return [...list].sort((a, b) => {
            const gramsA = a.grams ?? Number.MAX_SAFE_INTEGER;
            const gramsB = b.grams ?? Number.MAX_SAFE_INTEGER;
            return gramsA - gramsB;
        });
    }

    const orderedChoices = sortChoicesByGrams(choices);

    const unitPriceEl = document.querySelector(root.dataset.unitPriceTarget);
    const originalPriceEl = document.querySelector(root.dataset.originalPriceTarget);
    const totalEl = document.querySelector(root.dataset.totalTarget);
    const hiddenInput = document.querySelector(root.dataset.hiddenInputTarget);
    const quantityInput = document.querySelector(root.dataset.quantityTarget);
    const orderLink = document.querySelector(root.dataset.orderLinkTarget);
    const startingLabel = document.querySelector(root.dataset.startingLabelTarget);
    const weightLabelEl = document.querySelector(root.dataset.weightLabelTarget);
    const personCapacityEl = document.querySelector(root.dataset.personCapacityTarget);
    const options = root.querySelectorAll('[data-variant-option]');
    const pills = root.querySelectorAll('[data-variant-id]');

    const symbol = root.dataset.currencySymbol || '₹';
    let couponDiscount = null;
    try {
        couponDiscount = root.dataset.couponDiscount ? JSON.parse(root.dataset.couponDiscount) : null;
    } catch {
        couponDiscount = null;
    }

    function applyCouponDiscount(price) {
        const subtotal = Number(price);
        if (!couponDiscount || subtotal <= 0) return subtotal;
        if (couponDiscount.type === 'fixed') {
            return Math.max(0, subtotal - Number(couponDiscount.amount));
        }
        const raw = subtotal * (Number(couponDiscount.amount) / 100);
        const cap = couponDiscount.max_cap != null ? Number(couponDiscount.max_cap) : raw;
        return Math.max(0, subtotal - Math.min(raw, cap, subtotal));
    }

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

        if (options.length) {
            options.forEach((option) => {
                const pill = option.querySelector('[data-variant-id]');
                if (!pill) return;

                const active = parseInt(pill.dataset.variantId, 10) === id;
                pill.setAttribute('aria-pressed', active ? 'true' : 'false');

                const capacityEl = option.querySelector('[data-variant-capacity]');
                if (capacityEl) {
                    capacityEl.classList.toggle('hidden', !active);
                }
            });
        } else {
            pills.forEach((pill) => {
                const active = parseInt(pill.dataset.variantId, 10) === id;
                pill.setAttribute('aria-pressed', active ? 'true' : 'false');
            });
        }

        if (unitPriceEl) {
            const discounted = applyCouponDiscount(choice.price);
            unitPriceEl.textContent = formatMoney(discounted);
            if (originalPriceEl && couponDiscount) {
                originalPriceEl.textContent = formatMoney(choice.price);
                originalPriceEl.classList.remove('hidden');
            } else if (originalPriceEl) {
                originalPriceEl.classList.add('hidden');
            }
        }
        if (totalEl) totalEl.textContent = formatMoney(applyCouponDiscount(choice.price) * getQuantity());
        if (weightLabelEl) weightLabelEl.textContent = choice.label;
        if (personCapacityEl) {
            const capacity = choice.person_capacity_label?.trim();
            if (capacity) {
                personCapacityEl.textContent = capacity;
                personCapacityEl.classList.remove('hidden');
            } else {
                personCapacityEl.textContent = '';
                personCapacityEl.classList.add('hidden');
            }
        }
        if (hiddenInput) hiddenInput.value = id;
        if (startingLabel) startingLabel.classList.add('hidden');
        if (orderLink && orderLink.dataset.baseUrl) {
            const url = new URL(orderLink.dataset.baseUrl, window.location.origin);
            url.searchParams.set('product_variant_id', id);
            orderLink.href = url.pathname + url.search;
        }

        document.dispatchEvent(new CustomEvent('variant-price-changed'));
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

    const initialId = parseInt(
        root.dataset.initialVariantId || orderedChoices[0]?.id || choices.find((c) => c.is_default)?.id || choices[0]?.id,
        10
    );
    if (initialId) selectVariant(initialId);
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-variant-picker]').forEach((root) => initProductVariantPicker(root));
});
