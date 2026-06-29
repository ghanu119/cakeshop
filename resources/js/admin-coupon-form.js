import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';

select2($);

function initCouponMultiSelect(selector) {
    const $select = $(selector);
    if (!$select.length || $select.hasClass('select2-hidden-accessible')) {
        return;
    }

    $select.select2({
        width: '100%',
        multiple: true,
        placeholder: $select.data('placeholder') || '',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent: $select.parent(),
    });
}

document.addEventListener('DOMContentLoaded', () => {
    const form = document.querySelector('[data-coupon-form]');
    if (!form) return;

    const autoApply = form.querySelector('[data-auto-apply]');
    const scopeSections = form.querySelector('[data-scope-sections]');
    const discountType = form.querySelector('[data-discount-type]');
    const maxDiscountWrap = form.querySelector('[data-max-discount-wrap]');
    const productScopeRadios = form.querySelectorAll('[data-product-scope]');
    const userScopeRadios = form.querySelectorAll('[data-user-scope]');
    const productIdsWrap = form.querySelector('[data-product-ids-wrap]');
    const categoryIdsWrap = form.querySelector('[data-category-ids-wrap]');
    const userIdsWrap = form.querySelector('[data-user-ids-wrap]');

    const initVisibleMultiSelects = () => {
        if (productIdsWrap && !productIdsWrap.classList.contains('hidden')) {
            initCouponMultiSelect('#product_ids');
        }
        if (categoryIdsWrap && !categoryIdsWrap.classList.contains('hidden')) {
            initCouponMultiSelect('#category_ids');
        }
        if (userIdsWrap && !userIdsWrap.classList.contains('hidden')) {
            initCouponMultiSelect('#user_ids');
        }
    };

    const toggleAutoApply = () => {
        if (!scopeSections || !autoApply) return;
        scopeSections.classList.toggle('hidden', autoApply.checked);
        if (!autoApply.checked) {
            initVisibleMultiSelects();
        }
    };

    const toggleMaxDiscount = () => {
        if (!maxDiscountWrap || !discountType) return;
        maxDiscountWrap.classList.toggle('hidden', discountType.value !== 'percentage');
    };

    const selectedProductScope = () => {
        const checked = form.querySelector('[data-product-scope]:checked');
        return checked ? checked.value : 'all';
    };

    const selectedUserScope = () => {
        const checked = form.querySelector('[data-user-scope]:checked');
        return checked ? checked.value : 'all';
    };

    const toggleProductScope = () => {
        const scope = selectedProductScope();
        productIdsWrap?.classList.toggle('hidden', scope !== 'products');
        categoryIdsWrap?.classList.toggle('hidden', scope !== 'categories');
        if (scope === 'products') {
            initCouponMultiSelect('#product_ids');
        }
        if (scope === 'categories') {
            initCouponMultiSelect('#category_ids');
        }
    };

    const toggleUserScope = () => {
        const showUsers = selectedUserScope() === 'users';
        userIdsWrap?.classList.toggle('hidden', !showUsers);
        if (showUsers) {
            initCouponMultiSelect('#user_ids');
        }
    };

    autoApply?.addEventListener('change', toggleAutoApply);
    discountType?.addEventListener('change', toggleMaxDiscount);
    productScopeRadios.forEach((radio) => radio.addEventListener('change', toggleProductScope));
    userScopeRadios.forEach((radio) => radio.addEventListener('change', toggleUserScope));

    toggleAutoApply();
    toggleMaxDiscount();
    toggleProductScope();
    toggleUserScope();
    initVisibleMultiSelects();
});
