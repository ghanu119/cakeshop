<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            'site_name' => 'Better Buns',
            'theme' => 'better-buns',
            'address' => 'B/H Asopalav Triangle Opp Matuki Restaurant Punit Nagar 80 Ft road Rajkot, Gujarat, Rajkot, Gujarat 360004',
            'contact' => '+918347991910',
            'admin_email' => 'admin@betterbuns.example.com',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'facebook_url' => 'https://www.facebook.com/',
            'instagram_url' => 'https://www.instagram.com/better_buns_live_bakery/',
            'twitter_url' => 'https://twitter.com/',
            'payment_instructions' => 'Please pay via UPI to the number shown, or transfer to our bank account. Mention your order ID in the payment note.',
            'payment_submit_instructions' => 'Share your transaction/UPI reference number, amount paid, and time of payment. You may upload a screenshot of the success screen.',
            'order_max_future_days' => '7',
            'order_min_hours_before_delivery' => '4',
            'kitchen_lead_hours' => '24',
            'message_on_cake_max_length' => '50',
        ];

        foreach ($items as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::flushCache();

        $logoPath = public_path('images/logo.jpg');
        if (is_file($logoPath)) {
            $siteSetting = SiteSetting::firstOrCreate([]);
            $siteSetting->clearMediaCollection('logo');
            $siteSetting->addMedia($logoPath)->preservingOriginal()->toMediaCollection('logo');
            $siteSetting->clearMediaCollection('header_icon');
            $siteSetting->addMedia($logoPath)->preservingOriginal()->toMediaCollection('header_icon');
        }
    }
}
