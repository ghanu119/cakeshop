<?php

namespace Database\Factories;

use App\Models\Slider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Slider>
 */
class SliderFactory extends Factory
{
    protected $model = Slider::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'is_system' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 10),
        ];
    }

    public function home(): static
    {
        return $this->state(fn () => [
            'name' => 'Home Slider',
            'slug' => Slider::SLUG_HOME,
            'description' => 'Homepage hero carousel for Better Buns theme',
            'is_system' => true,
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
