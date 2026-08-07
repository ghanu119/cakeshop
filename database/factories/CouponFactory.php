<?php

namespace Database\Factories;

use App\Models\Coupon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Coupon>
 */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('????##')),
            'label' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'from_date' => now()->subDay()->toDateString(),
            'to_date' => now()->addMonth()->toDateString(),
            'discount_type' => Coupon::DISCOUNT_PERCENTAGE,
            'discount_amount' => 10,
            'max_discount_amount' => 500,
            'status' => Coupon::STATUS_ACTIVE,
            'auto_apply' => false,
            'is_secret' => false,
            'min_order_amount' => null,
            'product_scope' => Coupon::PRODUCT_SCOPE_ALL,
            'user_scope' => Coupon::USER_SCOPE_ALL,
        ];
    }

    public function fixed(float $amount = 50): static
    {
        return $this->state(fn () => [
            'discount_type' => Coupon::DISCOUNT_FIXED,
            'discount_amount' => $amount,
            'max_discount_amount' => null,
        ]);
    }

    public function percentage(float $percent = 10, ?float $max = 500): static
    {
        return $this->state(fn () => [
            'discount_type' => Coupon::DISCOUNT_PERCENTAGE,
            'discount_amount' => $percent,
            'max_discount_amount' => $max,
        ]);
    }

    public function autoApply(): static
    {
        return $this->state(fn () => [
            'auto_apply' => true,
            'product_scope' => Coupon::PRODUCT_SCOPE_ALL,
            'user_scope' => Coupon::USER_SCOPE_ALL,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['status' => Coupon::STATUS_INACTIVE]);
    }

    public function secret(): static
    {
        return $this->state(fn () => ['is_secret' => true]);
    }
}
