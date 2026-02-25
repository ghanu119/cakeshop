<?php

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Premium Quality',
                'description' => 'Made with finest ingredients and traditional recipes',
                'icon' => 'shield-check',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Fresh Daily',
                'description' => 'Baked fresh every morning for maximum flavor',
                'icon' => 'clock',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Custom Orders',
                'description' => 'Personalized cakes for your special occasions',
                'icon' => 'check-circle',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Fast Delivery',
                'description' => 'Quick and reliable delivery to your doorstep',
                'icon' => 'shopping-cart',
                'sort_order' => 4,
                'is_active' => true,
            ],
        ];

        foreach ($items as $item) {
            Feature::firstOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
