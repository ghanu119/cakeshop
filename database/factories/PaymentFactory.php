<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'payment_gateway' => 'razorpay',
            'gateway_order_id' => 'order_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
            'gateway_payment_id' => null,
            'signature' => null,
            'amount' => 500,
            'currency' => 'INR',
            'status' => PaymentStatus::Pending,
            'response_payload' => null,
            'paid_at' => null,
            'failed_at' => null,
            'metadata' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'gateway_payment_id' => 'pay_'.fake()->unique()->regexify('[A-Za-z0-9]{14}'),
            'signature' => fake()->sha256(),
            'status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => PaymentStatus::Failed,
            'failed_at' => now(),
            'metadata' => ['failure_code' => 'payment_failed'],
        ]);
    }
}
