<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'site_name' => 'Sweet Delights',
            'address' => '123 Bakery Lane, Near Central Market, City – 400001',
            'contact' => '+91 98765 43210',
            'admin_email' => 'admin@sweetdelights.example.com',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'facebook_url' => 'https://www.facebook.com/',
            'instagram_url' => 'https://www.instagram.com/',
            'twitter_url' => 'https://twitter.com/',
            'payment_instructions' => 'Please pay via UPI to the number shown, or transfer to our bank account. Mention your order ID in the payment note.',
            'payment_submit_instructions' => 'Share your transaction/UPI reference number, amount paid, and time of payment. You may upload a screenshot of the success screen.',
            'order_max_future_days' => '7',
            'order_min_hours_before_delivery' => '4',
            'kitchen_lead_hours' => '24',
        ];

        foreach ($items as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::flushCache();
    }
}
