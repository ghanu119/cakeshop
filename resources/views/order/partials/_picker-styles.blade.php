@once
    @push('styles')
    <style>
        .variant-picker [data-variant-id],
        .flavor-picker [data-flavor-id],
        .fulfillment-picker [data-fulfillment-type] {
            cursor: pointer;
            transition: background-color 150ms ease, border-color 150ms ease, box-shadow 150ms ease, color 150ms ease;
        }
        .variant-picker [data-variant-id][aria-pressed="true"] {
            background-color: #fef3c7 !important;
            border-color: #f59e0b !important;
            box-shadow: 0 0 0 2px #f59e0b;
            color: #78350f !important;
            font-weight: 600;
        }
        .variant-picker [data-variant-option] {
            gap: 0;
        }
        .variant-picker [data-variant-capacity] {
            margin: 0.375rem 0 0;
            padding: 0;
            line-height: 1.2;
        }
        .flavor-picker [data-flavor-id][aria-pressed="true"] {
            background-color: #fff1f2 !important;
            border-color: #fb7185 !important;
            box-shadow: 0 0 0 2px #fb7185;
            color: #9f1239 !important;
            font-weight: 600;
        }
        .flavor-picker.flavor-picker--error {
            outline: 2px solid #ef4444;
            outline-offset: 4px;
            border-radius: 0.5rem;
            padding: 0.25rem;
        }
        .flavor-picker.flavor-picker--error [data-flavor-id] {
            border-color: #fca5a5;
        }
        [data-flavor-summary].is-empty {
            border-style: dashed;
            border-color: #fda4af;
            background-color: #fff1f2;
        }
        .fulfillment-picker [data-fulfillment-type][aria-pressed="true"] {
            background-color: #f0fdfa !important;
            border-color: #14b8a6 !important;
            box-shadow: 0 0 0 2px #14b8a6;
            color: #134e4a !important;
            font-weight: 600;
        }

        /* Pincode Select2 — restyled to match the surrounding form fields */
        .pincode-select .select2-container {
            width: 100% !important;
        }
        .pincode-select .select2-container--default .select2-selection--single {
            height: auto;
            display: flex;
            align-items: center;
            border: 1px solid #e7e5e4;
            border-radius: 0.75rem;
            background-color: #fff;
            padding: 0.75rem 2.5rem 0.75rem 1rem;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
        }
        .pincode-select .select2-container--default.select2-container--open .select2-selection--single,
        .pincode-select .select2-container--default.select2-container--focus .select2-selection--single {
            border-color: #14b8a6;
            background-color: #fff;
            box-shadow: 0 0 0 2px rgba(20, 184, 166, 0.2);
            outline: none;
        }
        .pincode-select .select2-container--default.select2-container--disabled .select2-selection--single {
            background-color: #f5f5f4;
            color: #a8a29e;
            cursor: not-allowed;
        }
        .pincode-select .select2-container--default .select2-selection--single .select2-selection__rendered {
            padding: 0;
            line-height: 1.5;
            font-size: 1rem;
            color: #1c1917;
            flex: 1 1 auto;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .pincode-select .select2-selection__placeholder {
            color: #a8a29e;
        }
        .pincode-select .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100%;
            top: 0;
            right: 0.5rem;
            width: 1.25rem;
            pointer-events: none;
        }
        .pincode-select .select2-selection__arrow b {
            border-color: #78716c transparent transparent transparent;
        }
        .pincode-select .select2-container--open .select2-selection__arrow b {
            border-color: transparent transparent #78716c transparent;
        }
        .pincode-select .select2-container--default .select2-selection--single .select2-selection__clear {
            float: right;
            margin-right: 0.5rem;
            color: #a8a29e;
            font-size: 1.125rem;
            font-weight: 400;
            line-height: 1.5;
        }
        .pincode-select.is-success .select2-container--default .select2-selection--single {
            border-color: #6ee7b7;
            background-color: #ecfdf5;
        }
        .pincode-select.is-error .select2-container--default .select2-selection--single {
            border-color: #fca5a5;
            background-color: #fef2f2;
        }
        .select2-dropdown.pincode-select-dropdown {
            border-radius: 0.75rem;
            border-color: #e7e5e4;
            box-shadow: 0 10px 30px rgba(28, 25, 23, 0.14);
            overflow: hidden;
            margin-top: 0.25rem;
        }
        .select2-dropdown.pincode-select-dropdown .select2-search--dropdown {
            padding: 0.5rem;
        }
        .select2-dropdown.pincode-select-dropdown .select2-search__field {
            border-radius: 0.5rem;
            border: 1px solid #e7e5e4;
            padding: 0.5rem 0.75rem;
            outline: none;
        }
        .select2-dropdown.pincode-select-dropdown .select2-search__field:focus {
            border-color: #14b8a6;
        }
        .select2-dropdown.pincode-select-dropdown .select2-results__option {
            padding: 0.625rem 0.875rem;
            font-size: 0.9375rem;
        }
        .select2-dropdown.pincode-select-dropdown .select2-results__option--highlighted[aria-selected] {
            background-color: #f0fdfa;
            color: #134e4a;
        }
        .select2-dropdown.pincode-select-dropdown .select2-results__option[aria-selected="true"] {
            background-color: #ccfbf1;
            color: #134e4a;
            font-weight: 600;
        }
    </style>
    @endpush
@endonce
