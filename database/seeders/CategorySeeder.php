<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name_en' => 'Birthday Cakes', 'slug' => 'birthday-cakes', 'status' => 'active', 'sort_order' => 1],
            ['name_en' => 'Wedding Cakes', 'slug' => 'wedding-cakes', 'status' => 'active', 'sort_order' => 2],
            ['name_en' => 'Custom Cakes', 'slug' => 'custom-cakes', 'status' => 'active', 'sort_order' => 3],
            ['name_en' => 'Cupcakes', 'slug' => 'cupcakes', 'status' => 'active', 'sort_order' => 4],
            ['name_en' => 'Pastries & Desserts', 'slug' => 'pastries-desserts', 'status' => 'active', 'sort_order' => 5],
            ['name_en' => 'Seasonal Specials', 'slug' => 'seasonal-specials', 'status' => 'active', 'sort_order' => 6],
        ];

        foreach ($items as $item) {
            Category::firstOrCreate(
                ['slug' => $item['slug']],
                array_merge($item, ['name_hi' => null, 'name_gu' => null])
            );
        }
    }
}
