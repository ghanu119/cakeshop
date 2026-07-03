<?php

namespace App\Http\Requests\Admin;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('settings.manage') ?? false;
    }

    public function rules(): array
    {
        $availableThemes = array_keys(config('themes.available', ['warm' => []]));

        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'theme' => ['nullable', 'string', 'in:'.implode(',', $availableThemes)],
            'address' => ['nullable', 'string'],
            'contact' => ['nullable', 'string', 'max:500'],
            'admin_email' => ['nullable', 'email'],
            'google_map_iframe' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'payment_upi_id' => ['nullable', 'string', 'max:255'],
            'payment_submit_instructions' => ['nullable', 'string'],
            'payment_gateway' => ['nullable', 'string', 'in:razorpay'],
            'razorpay_key_id' => ['nullable', 'string', 'max:255'],
            'razorpay_key_secret' => ['nullable', 'string', 'max:255'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'kitchen_lead_hours' => ['nullable', 'integer', 'min:0'],
            'order_max_future_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'order_min_hours_before_delivery' => ['nullable', 'integer', 'min:0', 'max:72'],
            'message_on_cake_max_length' => ['nullable', 'integer', 'min:'.Order::MESSAGE_ON_CAKE_MIN_LENGTH, 'max:'.Order::MESSAGE_ON_CAKE_LIMIT_MAX],
            'checkout_delivery_notice' => ['nullable', 'string', 'max:500'],
            'checkout_takeaway_notice' => ['nullable', 'string', 'max:500'],
            'checkout_takeaway_address' => ['nullable', 'string', 'max:1000'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],
            'header_icon' => ['nullable', 'image', 'max:1024'],
            'facebook_url' => ['nullable', 'string', 'max:500', 'url'],
            'instagram_url' => ['nullable', 'string', 'max:500', 'url'],
            'twitter_url' => ['nullable', 'string', 'max:500', 'url'],
            'product_note' => ['nullable', 'string', 'max:1000'],
            'notifications_enabled' => ['nullable', 'boolean'],
            'notifications_web_push_enabled' => ['nullable', 'boolean'],
            'pusher_app_id' => ['nullable', 'string', 'max:255'],
            'pusher_app_key' => ['nullable', 'string', 'max:255'],
            'pusher_app_secret' => ['nullable', 'string', 'max:255'],
            'pusher_app_cluster' => ['nullable', 'string', 'max:50'],
        ];
    }
}
