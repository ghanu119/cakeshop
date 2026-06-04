import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';

select2($);

function initProductFilterMultiSelect(selector) {
    const $select = $(selector);
    if (!$select.length) {
        return;
    }

    const $form = $select.closest('#product-filters');
    const dropdownParent = $form.length ? $form : $select.parent();

    $select.select2({
        width: '100%',
        multiple: true,
        placeholder: $select.data('placeholder') || '',
        allowClear: true,
        closeOnSelect: false,
        dropdownParent,
    });
}

document.addEventListener('DOMContentLoaded', function () {
    initProductFilterMultiSelect('#flavor_ids');
    initProductFilterMultiSelect('#weight_ids');
});
