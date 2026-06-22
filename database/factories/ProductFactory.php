<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);
        return [
            'category_id' => Category::factory(),
            'name_en' => $name,
            'name_hi' => null,
            'name_gu' => null,
            'slug' => Str::slug($name),
            'description_en' => fake()->paragraph(),
            'description_hi' => null,
            'description_gu' => null,
            'short_description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 100, 5000),
            'status' => 'active',
            'meta_title' => null,
            'meta_description' => null,
            'show_on_homepage' => false,
            'is_highlight' => false,
            'is_trending' => false,
            'is_featured' => false,
            'homepage_sort_order' => null,
            'sku' => fake()->unique()->bothify('CAKE-???-###'),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'inactive']);
    }
}
