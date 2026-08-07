import $ from 'jquery';
import select2 from 'select2/dist/js/select2.full';
import 'select2/dist/css/select2.min.css';

select2($);

document.addEventListener('DOMContentLoaded', function () {
    const $pincodeSelect = $('[data-delivery-pincode-input]');
    if (!$pincodeSelect.length || $pincodeSelect.hasClass('select2-hidden-accessible')) {
        return;
    }

    $pincodeSelect.select2({
        width: '100%',
        placeholder: $pincodeSelect.data('placeholder') || '',
        allowClear: true,
        dropdownParent: $pincodeSelect.parent(),
        dropdownCssClass: 'pincode-select-dropdown',
    });
});
