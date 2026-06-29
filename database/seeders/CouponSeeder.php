<?php

namespace Database\Seeders;

use App\Models\Coupon;
use Illuminate\Database\Seeder;

class CouponSeeder extends Seeder
{
    public function run(): void
    {
        Coupon::firstOrCreate(
            ['code' => 'WELCOME10'],
            [
                'label' => 'Welcome 10% Off',
                'description' => 'Get 10% off your first order.',
                'from_date' => now()->subDay()->toDateString(),
                'to_date' => now()->addMonths(3)->toDateString(),
                'discount_type' => Coupon::DISCOUNT_PERCENTAGE,
                'discount_amount' => 10,
                'max_discount_amount' => 300,
                'status' => Coupon::STATUS_ACTIVE,
                'auto_apply' => true,
                'product_scope' => Coupon::PRODUCT_SCOPE_ALL,
                'user_scope' => Coupon::USER_SCOPE_ALL,
            ]
        );
    }
}
