<?php

namespace Database\Factories;

use App\Models\Flavor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Flavor>
 */
class FlavorFactory extends Factory
{
    protected $model = Flavor::class;

    public function definition(): array
    {
        $name = fake()->unique()->word();

        return [
            'name_en' => ucfirst($name),
            'name_hi' => null,
            'name_gu' => null,
            'slug' => Str::slug($name),
            'status' => 'active',
            'sort_order' => fake()->numberBetween(0, 20),
            'badge_color' => 'rose',
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }
}
