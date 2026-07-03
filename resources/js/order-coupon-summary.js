document.addEventListener('DOMContentLoaded', () => {
    const section = document.querySelector('[data-order-coupon-section]');
    if (!section) return;

    const form = section.closest('form');
    const validateUrl = section.dataset.validateUrl;
    const csrf = section.dataset.csrf;
    const defaultCouponCode = section.dataset.defaultCouponCode || '';
    const currencySymbol = section.dataset.currencySymbol || document.querySelector('[data-variant-picker]')?.dataset.currencySymbol || '₹';
    const maxDiscountTemplate = section.dataset.maxDiscountTemplate || 'Maximum discount of :amount applies to this offer.';

    const codeInput = section.querySelector('[data-coupon-code-input]');
    const clearBtn = section.querySelector('[data-coupon-code-clear]');
    const applyBtn = section.querySelector('[data-coupon-apply-btn]');
    const appliedBadge = section.querySelector('[data-coupon-applied-badge]');
    const codeInputGroup = section.querySelector('[data-coupon-code-wrap]');
    const codeFeedback = section.querySelector('[data-coupon-code-feedback]');
    const declinedInput = section.querySelector('[data-coupon-declined]');
    const subtotalEl = section.querySelector('[data-summary-subtotal]');
    const discountRow = section.querySelector('[data-summary-discount-row]');
    const discountEl = section.querySelector('[data-summary-discount]');
    const discountLabel = section.querySelector('[data-summary-discount-label]');
    const summaryMaxDiscountWrap = section.querySelector('[data-summary-max-discount-wrap]');
    const summaryMaxDiscountPopover = summaryMaxDiscountWrap?.querySelector('[data-max-discount-popover]');
    const totalEl = section.querySelector('[data-summary-total]');
    const offersPanel = section.querySelector('[data-coupon-offers-panel]');

    const applyLabel = applyBtn?.dataset.labelApply || 'Apply';
    const appliedLabel = applyBtn?.dataset.labelApplied || 'Applied';
    const recommendedLabel = section.dataset.recommendedLabel || 'Recommended';
    const saveLabel = section.dataset.saveLabel || 'Save';
    const availableOffersLabel = section.dataset.availableOffersLabel || 'Available offers';
    const pickOneLabel = section.dataset.pickOneLabel || 'Pick one';

    let debounceTimer = null;
    let codeApplied = false;

    const formatMoney = (amount) => currencySymbol + Number(amount).toFixed(2);

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;');

    const formatMaxDiscountMessage = (maxCap) => {
        const amount = formatMoney(maxCap);
        return maxDiscountTemplate.replace(':amount', amount);
    };

    const isCouponDeclined = () => declinedInput?.value === '1';

    const setCouponDeclined = (declined) => {
        if (declinedInput) {
            declinedInput.value = declined ? '1' : '0';
        }
    };

    const closeAllPopovers = () => {
        section.querySelectorAll('[data-max-discount-info].is-open').forEach((wrap) => {
            wrap.classList.remove('is-open');
            wrap.querySelector('[data-max-discount-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    const initMaxDiscountPopovers = () => {
        section.querySelectorAll('[data-max-discount-info]').forEach((wrap) => {
            const trigger = wrap.querySelector('[data-max-discount-trigger]');
            if (!trigger || trigger.dataset.popoverBound === 'true') {
                return;
            }

            trigger.dataset.popoverBound = 'true';
            trigger.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();

                const isOpen = wrap.classList.contains('is-open');
                closeAllPopovers();

                if (!isOpen) {
                    wrap.classList.add('is-open');
                    trigger.setAttribute('aria-expanded', 'true');
                }
            });
        });
    };

    document.addEventListener('click', (event) => {
        if (!event.target.closest('[data-max-discount-info]')) {
            closeAllPopovers();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllPopovers();
        }
    });

    const showCodeFeedback = (message, type = 'error') => {
        if (!codeFeedback) return;

        if (!message) {
            codeFeedback.textContent = '';
            codeFeedback.classList.add('hidden');
            codeFeedback.classList.remove('text-red-600', 'text-emerald-700', 'text-amber-800');
            return;
        }

        codeFeedback.textContent = message;
        codeFeedback.classList.remove('hidden', 'text-red-600', 'text-emerald-700', 'text-amber-800');
        if (type === 'success') {
            codeFeedback.classList.add('text-emerald-700');
        } else if (type === 'info') {
            codeFeedback.classList.add('text-amber-800');
        } else {
            codeFeedback.classList.add('text-red-600');
        }
    };

    const updateClearVisibility = () => {
        if (!clearBtn || !codeInput) return;
        const hasValue = codeInput.value.trim() !== '';
        clearBtn.classList.toggle('is-hidden', !hasValue);
        clearBtn.setAttribute('aria-hidden', hasValue ? 'false' : 'true');
    };

    const setAppliedState = (applied) => {
        codeApplied = applied;

        if (appliedBadge) {
            appliedBadge.classList.toggle('hidden', !applied);
            appliedBadge.classList.toggle('inline-flex', applied);
        }

        if (codeInputGroup) {
            codeInputGroup.classList.toggle('is-applied', applied);
        }

        if (!applyBtn) return;

        applyBtn.textContent = applied ? appliedLabel : applyLabel;
        applyBtn.classList.toggle('is-applied', applied);
        applyBtn.disabled = applied;
        applyBtn.setAttribute('aria-pressed', applied ? 'true' : 'false');
    };

    const syncOfferSelection = (code) => {
        const normalized = (code || '').trim().toUpperCase();
        section.querySelectorAll('[data-coupon-radio]').forEach((radio) => {
            const radioCode = (radio.dataset.couponCode || radio.value || '').trim().toUpperCase();
            radio.checked = normalized !== '' && radioCode === normalized;
        });
    };

    const renderMaxDiscountInfoHtml = (maxCap) => {
        const cap = Number(maxCap ?? 0);
        if (cap <= 0) {
            return '';
        }

        const message = escapeHtml(formatMaxDiscountMessage(cap));

        return `<span class="coupon-max-discount-info relative inline-flex shrink-0 align-middle" data-max-discount-info>
            <button type="button" class="coupon-max-discount-info__trigger inline-flex h-4 w-4 items-center justify-center rounded-full text-stone-400 hover:text-stone-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-amber-500/50" data-max-discount-trigger aria-expanded="false" aria-label="Maximum discount information">
                <svg class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
            </button>
            <span class="coupon-max-discount-info__popover" data-max-discount-popover role="tooltip">${message}</span>
        </span>`;
    };

    const renderOfferRow = (offer, bestCouponId, selectedCode) => {
        const isSelected = String(selectedCode || '').trim().toUpperCase() === String(offer.code || '').trim().toUpperCase();
        const isRecommended = String(bestCouponId ?? '') === String(offer.id ?? '');
        const description = offer.description
            ? `<span class="mt-1 block text-xs leading-relaxed text-stone-500">${escapeHtml(offer.description)}</span>`
            : '';
        const recommendedBadge = isRecommended
            ? `<span class="inline-flex items-center rounded-md bg-emerald-100 px-1.5 py-0.5 text-[10px] font-bold uppercase tracking-wide text-emerald-800">${escapeHtml(recommendedLabel)}</span>`
            : '';

        return `<label class="group relative flex cursor-pointer gap-3 px-4 py-3.5 transition-colors duration-150 hover:bg-stone-50/80 has-[:checked]:bg-amber-50/60">
            <span class="absolute inset-y-0 left-0 w-1 rounded-r bg-amber-500 opacity-0 transition-opacity group-has-[:checked]:opacity-100" aria-hidden="true"></span>
            <input type="radio" name="coupon_offer" value="${escapeHtml(offer.code)}" class="mt-0.5 h-4 w-4 shrink-0 border-stone-300 text-amber-600 focus:ring-amber-500" ${isSelected ? 'checked' : ''} data-coupon-radio data-coupon-code="${escapeHtml(offer.code)}" data-discount-amount="${escapeHtml(offer.discount_amount ?? 0)}" />
            <span class="min-w-0 flex-1">
                <span class="flex items-start justify-between gap-3">
                    <span class="min-w-0">
                        <span class="flex flex-wrap items-center gap-x-1.5 gap-y-1">
                            <span class="font-semibold leading-tight text-stone-900">${escapeHtml(offer.label)}</span>
                            ${renderMaxDiscountInfoHtml(offer.max_cap)}
                            ${recommendedBadge}
                        </span>
                        ${description}
                    </span>
                    <span class="shrink-0 rounded-lg bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-900">${escapeHtml(offer.badge_text || '')}</span>
                </span>
                <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-emerald-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    ${escapeHtml(saveLabel)} ${escapeHtml(formatMoney(offer.discount_amount ?? 0))}
                </span>
            </span>
        </label>`;
    };

    const updateOfferPicker = (coupons, bestCouponId, selectedCode) => {
        if (!offersPanel) {
            return;
        }

        const list = Array.isArray(coupons) ? coupons : [];

        if (list.length < 2) {
            offersPanel.classList.add('hidden');
            offersPanel.setAttribute('aria-hidden', 'true');
            offersPanel.innerHTML = '';
            return;
        }

        offersPanel.classList.remove('hidden');
        offersPanel.setAttribute('aria-hidden', 'false');
        offersPanel.classList.add('overflow-hidden', 'rounded-2xl', 'border', 'border-stone-200/80', 'bg-white', 'shadow-sm');
        offersPanel.innerHTML = `<div class="flex items-center justify-between gap-3 border-b border-stone-100 bg-gradient-to-r from-amber-50/80 to-white px-4 py-3">
            <div class="flex items-center gap-2.5">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white shadow-sm" aria-hidden="true">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </span>
                <p class="text-sm font-bold text-stone-900">${escapeHtml(availableOffersLabel)}</p>
            </div>
            <span class="text-[11px] font-semibold uppercase tracking-wide text-stone-400">${escapeHtml(pickOneLabel)}</span>
        </div>
        <div class="divide-y divide-stone-100" role="radiogroup" aria-label="${escapeHtml(availableOffersLabel)}" data-coupon-offers-list>${list.map((offer) => renderOfferRow(offer, bestCouponId, selectedCode)).join('')}</div>`;
        initMaxDiscountPopovers();
        syncOfferSelection(selectedCode || codeInput?.value || '');
    };

    const getPayload = (includePendingCode = false) => {
        const variantInput = form?.querySelector('#product_variant_id');
        const quantityInput = form?.querySelector('#quantity');
        const emailInput = form?.querySelector('#guest_email');
        const manualCode = codeInput?.value?.trim() || null;
        const useCode = Boolean(manualCode) && (codeApplied || includePendingCode);

        return {
            product_variant_id: variantInput?.value || null,
            quantity: quantityInput?.value || 1,
            guest_email: emailInput?.value?.trim() || null,
            coupon_code: useCode ? manualCode : null,
        };
    };

    const updateSummaryMaxDiscountInfo = (maxCap) => {
        if (!summaryMaxDiscountWrap) return;

        const cap = Number(maxCap ?? 0);
        if (cap > 0) {
            if (summaryMaxDiscountPopover) {
                summaryMaxDiscountPopover.textContent = formatMaxDiscountMessage(cap);
            }
            summaryMaxDiscountWrap.classList.remove('hidden');
            summaryMaxDiscountWrap.classList.add('inline-flex');
        } else {
            summaryMaxDiscountWrap.classList.add('hidden');
            summaryMaxDiscountWrap.classList.remove('inline-flex');
        }
    };

    const updateSummary = (data) => {
        const displayTotal = data.total ?? data.subtotal ?? 0;
        const estimatedTotalEl = document.getElementById('order-estimated-total');

        if (estimatedTotalEl) {
            estimatedTotalEl.textContent = formatMoney(displayTotal);
        }

        if (!subtotalEl || !totalEl) return;

        subtotalEl.textContent = formatMoney(data.subtotal ?? 0);
        totalEl.textContent = formatMoney(displayTotal);

        const discount = Number(data.discount_amount ?? 0);
        if (discountRow && discountEl) {
            if (discount > 0) {
                discountRow.classList.remove('hidden');
                discountEl.textContent = '−' + formatMoney(discount).replace(/^[^\d-]+/, (match) => match);
                if (discountLabel && data.label) {
                    discountLabel.textContent = data.label;
                }
                updateSummaryMaxDiscountInfo(data.max_cap);
            } else {
                discountRow.classList.add('hidden');
                updateSummaryMaxDiscountInfo(null);
            }
        }
    };

    const refresh = (options = {}) => {
        if (!validateUrl) return;

        const payload = getPayload(options.fromCodeApply);
        const body = new URLSearchParams();

        if (payload.product_variant_id) body.set('product_variant_id', payload.product_variant_id);
        body.set('quantity', payload.quantity);
        if (payload.guest_email) body.set('guest_email', payload.guest_email);
        if (payload.coupon_code) body.set('coupon_code', payload.coupon_code);

        if (options.skipAutoApply || isCouponDeclined()) {
            body.set('skip_auto_apply', '1');
        } else if (options.reapplyDefault && !payload.coupon_code) {
            body.set('auto_select_best', '1');
        }

        if (applyBtn && !codeApplied) {
            applyBtn.disabled = true;
        }

        fetch(validateUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: body.toString(),
        })
            .then((r) => r.json())
            .then((data) => {
                if (options.reapplyDefault && !codeApplied && data.best_coupon_code && !isCouponDeclined()) {
                    if (codeInput) {
                        codeInput.value = data.best_coupon_code;
                    }
                    updateClearVisibility();
                    syncOfferSelection(data.best_coupon_code);
                    setAppliedState(true);
                    showCodeFeedback(null);
                    return refresh({ fromCodeApply: true });
                }

                if (data.coupon_code && codeApplied && codeInput) {
                    codeInput.value = data.coupon_code;
                    syncOfferSelection(data.coupon_code);
                }

                updateOfferPicker(
                    data.universal_coupons,
                    data.best_coupon_id,
                    codeApplied ? (data.coupon_code || codeInput?.value || '') : '',
                );
                updateSummary(data);

                if (options.fromCodeApply) {
                    if (data.valid) {
                        setAppliedState(true);
                        setCouponDeclined(false);
                        syncOfferSelection(data.coupon_code || codeInput?.value);
                        const message = data.label
                            ? `${data.label} · ${data.message || ''}`.replace(/\s+·\s*$/, '')
                            : (data.message || 'Coupon applied.');
                        showCodeFeedback(message, 'success');
                    } else if (payload.coupon_code) {
                        setAppliedState(false);
                        syncOfferSelection('');
                        const feedbackType = data.reason === 'sign_in_required' ? 'info' : 'error';
                        showCodeFeedback(
                            data.message || 'This coupon code is invalid or not applicable to your order.',
                            feedbackType,
                        );
                    } else {
                        showCodeFeedback(null);
                    }
                } else if (codeApplied && payload.coupon_code && !data.valid) {
                    setAppliedState(false);
                    syncOfferSelection('');
                    const feedbackType = data.reason === 'sign_in_required' ? 'info' : 'error';
                    showCodeFeedback(
                        data.message || 'This coupon code is no longer applicable to your order.',
                        feedbackType,
                    );
                }
            })
            .catch(() => {
                if (options.fromCodeApply) {
                    setAppliedState(false);
                    showCodeFeedback('Could not validate the coupon. Please try again.');
                }
            })
            .finally(() => {
                if (applyBtn && !codeApplied) {
                    applyBtn.disabled = false;
                }
            });
    };

    const scheduleRefresh = (options = {}) => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => refresh(options), 300);
    };

    const applyCouponCode = () => {
        if (codeApplied) {
            return;
        }

        const code = codeInput?.value?.trim() || '';

        if (!code) {
            showCodeFeedback('Please enter a coupon code.');
            codeInput?.focus();
            return;
        }

        setCouponDeclined(false);
        refresh({ fromCodeApply: true });
    };

    const clearCouponCode = () => {
        if (!codeInput) return;

        codeInput.value = '';
        setAppliedState(false);
        setCouponDeclined(true);
        showCodeFeedback(null);
        updateClearVisibility();
        syncOfferSelection('');
        codeInput.focus();
        refresh({ skipAutoApply: true });
    };

    applyBtn?.addEventListener('click', applyCouponCode);

    clearBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        clearCouponCode();
    });

    codeInput?.addEventListener('input', () => {
        updateClearVisibility();

        if (codeApplied) {
            setAppliedState(false);
            showCodeFeedback(null);
        }
    });

    codeInput?.addEventListener('paste', () => {
        requestAnimationFrame(updateClearVisibility);
    });

    codeInput?.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            applyCouponCode();
        }
    });

    const onOrderCompositionChanged = () => {
        setCouponDeclined(false);
        document.dispatchEvent(new CustomEvent('order-coupon:composition-changed'));

        if (codeApplied) {
            scheduleRefresh({ fromCodeApply: true });
        } else {
            scheduleRefresh({ reapplyDefault: true });
        }
    };

    offersPanel?.addEventListener('change', (event) => {
        const radio = event.target;
        if (!radio?.matches?.('[data-coupon-radio]') || !radio.checked || !codeInput) {
            return;
        }

        const offerCode = radio.dataset.couponCode || radio.value || '';
        codeInput.value = offerCode;
        setCouponDeclined(false);
        setAppliedState(true);
        showCodeFeedback(null);
        updateClearVisibility();
        closeAllPopovers();
        syncOfferSelection(offerCode);
        refresh({ fromCodeApply: true });
    });

    form?.querySelector('#quantity')?.addEventListener('input', onOrderCompositionChanged);

    form?.querySelector('#product_variant_id')?.addEventListener('change', onOrderCompositionChanged);

    document.addEventListener('variant-price-changed', onOrderCompositionChanged);

    initMaxDiscountPopovers();
    updateClearVisibility();

    document.addEventListener('order-coupon:invalidate', (event) => {
        const message = event.detail?.message;

        if (!message) {
            return;
        }

        setAppliedState(false);
        showCodeFeedback(message, 'error');
        codeInput?.focus();
    });

    const initialCode = codeInput?.value?.trim() || '';
    if (initialCode && !isCouponDeclined()) {
        codeApplied = true;
        setAppliedState(true);
        syncOfferSelection(initialCode);
        refresh({ fromCodeApply: true });
    } else if (isCouponDeclined()) {
        refresh({ skipAutoApply: true });
    } else {
        scheduleRefresh({ reapplyDefault: true });
    }
});
