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
        .flavor-picker [data-flavor-id][aria-pressed="true"] {
            background-color: #fff1f2 !important;
            border-color: #fb7185 !important;
            box-shadow: 0 0 0 2px #fb7185;
            color: #9f1239 !important;
            font-weight: 600;
        }
        .fulfillment-picker [data-fulfillment-type][aria-pressed="true"] {
            background-color: #f0fdfa !important;
            border-color: #14b8a6 !important;
            box-shadow: 0 0 0 2px #14b8a6;
            color: #134e4a !important;
            font-weight: 600;
        }
    </style>
    @endpush
@endonce
