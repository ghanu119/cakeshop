import $ from 'jquery';
import select2 from 'select2';
import 'select2/dist/css/select2.min.css';

select2($);

document.addEventListener('DOMContentLoaded', function () {
    const $flavorSelect = $('#flavor_ids');
    if (!$flavorSelect.length) {
        return;
    }

    $flavorSelect.select2({
        width: '100%',
        placeholder: 'Select flavors…',
        allowClear: true,
        dropdownParent: $flavorSelect.parent(),
    });
});
