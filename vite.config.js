import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin-select2.js',
                'resources/js/order-status-form.js',
                'resources/js/order-fulfillment-type.js',
                'resources/js/order-place-confirm.js',
            ],
            refresh: true,
        }),
    ],
});
