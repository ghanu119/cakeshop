<?php

use App\Models\Product;

if (! function_exists('whatsapp_login_enabled')) {
    /**
     * Whether WhatsApp OTP login/checkout is enabled and configured.
     * Config-only (no database access) so it is safe to call from Blade.
     */
    function whatsapp_login_enabled(): bool
    {
        return (bool) config('services.whatsapp.enabled')
            && ! empty(config('services.whatsapp.phone_number_id'))
            && ! empty(config('services.whatsapp.access_token'));
    }
}

if (! function_exists('whatsapp_url_for_contact')) {
    /**
     * Build a WhatsApp chat URL from a phone/contact string and optional pre-filled message.
     */
    function whatsapp_url_for_contact(?string $contact, ?string $message = null): ?string
    {
        if ($contact === null || trim($contact) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $contact);
        if ($digits === '') {
            return null;
        }

        $url = 'https://wa.me/'.$digits;

        if ($message !== null && trim($message) !== '') {
            $url .= '?text='.rawurlencode($message);
        }

        return $url;
    }
}

if (! function_exists('whatsapp_customize_help_url')) {
    /**
     * WhatsApp link for product customization help using site contact setting.
     */
    function whatsapp_customize_help_url(Product $product): ?string
    {
        $contact = settings('contact');
        $message = __('Hi, I need help customizing :product.', ['product' => $product->name_en]);

        return whatsapp_url_for_contact($contact, $message);
    }
}
