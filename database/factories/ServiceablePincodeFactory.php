<?php

namespace Database\Factories;

use App\Models\ServiceablePincode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceablePincode>
 */
class ServiceablePincodeFactory extends Factory
{
    protected $model = ServiceablePincode::class;

    public function definition(): array
    {
        return [
            'pincode' => fake()->unique()->numerify('3600##'),
            'locality' => fake()->streetName(),
            'city' => 'Rajkot',
            'state' => 'Gujarat',
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
