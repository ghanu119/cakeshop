<?php

namespace App\Http\Requests\Admin;

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
            'theme' => ['nullable', 'string', 'in:' . implode(',', $availableThemes)],
            'address' => ['nullable', 'string'],
            'contact' => ['nullable', 'string', 'max:500'],
            'admin_email' => ['nullable', 'email'],
            'google_map_iframe' => ['nullable', 'string'],
            'opening_hours' => ['nullable', 'string'],
            'payment_instructions' => ['nullable', 'string'],
            'payment_submit_instructions' => ['nullable', 'string'],
            'currency' => ['nullable', 'string', 'max:10'],
            'timezone' => ['nullable', 'string', 'max:50'],
            'kitchen_lead_hours' => ['nullable', 'integer', 'min:0'],
            'order_max_future_days' => ['nullable', 'integer', 'min:1', 'max:90'],
            'order_min_hours_before_delivery' => ['nullable', 'integer', 'min:0', 'max:72'],
            'payment_qr' => ['nullable', 'image', 'max:2048'],
            'header_icon' => ['nullable', 'image', 'max:1024'],
            'facebook_url' => ['nullable', 'string', 'max:500', 'url'],
            'instagram_url' => ['nullable', 'string', 'max:500', 'url'],
            'twitter_url' => ['nullable', 'string', 'max:500', 'url'],
            'product_note' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
