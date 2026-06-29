import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/admin.js',
                'resources/js/admin-select2.js',
                'resources/js/admin-product-images.js',
                'resources/js/admin-home-slider-image.js',
                'resources/js/admin-slider-item-form.js',
                'resources/js/admin-coupon-form.js',
                'resources/js/order-coupon-summary.js',
                'resources/js/product-gallery.js',
                'resources/js/order-status-form.js',
                'resources/js/order-fulfillment-type.js',
                'resources/js/order-pincode-check.js',
                'resources/js/order-place-confirm.js',
                'resources/js/order-delivery-datetime.js',
                'resources/js/image-lightbox.js',
                'resources/js/product-filters-select2.js',
                'resources/js/account-otp.js',
                'resources/js/account-register.js',
                'resources/js/admin-customer-lookup.js',
                'resources/js/admin-orders-index.js',
            ],
            refresh: true,
        }),
    ],
});
