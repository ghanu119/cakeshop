<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $tz = settings('timezone') ?? 'Asia/Kolkata';
        $deliveryAt = Carbon::now($tz)->addDay()->utc();

        return [
            'uuid' => (string) Str::uuid(),
            'guest_name' => fake()->name(),
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => fake()->numerify('##########'),
            'product_id' => Product::factory(),
            'product_name' => 'Test Cake',
            'quantity' => 1,
            'unit_price' => 500,
            'amount' => 500,
            'payment_status' => 'pending',
            'order_status' => 'pending',
            'delivery_at' => $deliveryAt,
            'ordered_at' => now(),
        ];
    }

    public function verified(): static
    {
        return $this->state(fn () => ['payment_status' => 'verified']);
    }

    public function processing(?Carbon $preparationAt = null): static
    {
        return $this->state(fn () => [
            'order_status' => 'processing',
            'preparation_at' => $preparationAt ?? now()->addHours(2),
        ]);
    }

    public function deliveryToday(): static
    {
        $tz = settings('timezone') ?? 'Asia/Kolkata';

        return $this->state(fn () => [
            'delivery_at' => Carbon::now($tz)->addHours(8)->utc(),
        ]);
    }
}
