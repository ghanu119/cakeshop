<?php

namespace Database\Seeders;

use App\Models\Flavor;
use Illuminate\Database\Seeder;

class FlavorSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['name_en' => 'Chocolate', 'slug' => 'chocolate', 'sort_order' => 1, 'badge_color' => 'rose'],
            ['name_en' => 'Vanilla', 'slug' => 'vanilla', 'sort_order' => 2, 'badge_color' => 'stone'],
            ['name_en' => 'Strawberry', 'slug' => 'strawberry', 'sort_order' => 3, 'badge_color' => 'rose'],
            ['name_en' => 'Red Velvet', 'slug' => 'red-velvet', 'sort_order' => 4, 'badge_color' => 'rose'],
            ['name_en' => 'Black Forest', 'slug' => 'black-forest', 'sort_order' => 5, 'badge_color' => 'stone'],
            ['name_en' => 'Butterscotch', 'slug' => 'butterscotch', 'sort_order' => 6, 'badge_color' => 'amber'],
            ['name_en' => 'Mango', 'slug' => 'mango', 'sort_order' => 7, 'badge_color' => 'amber'],
            ['name_en' => 'Pineapple', 'slug' => 'pineapple', 'sort_order' => 8, 'badge_color' => 'amber'],
        ];

        foreach ($items as $item) {
            Flavor::firstOrCreate(
                ['slug' => $item['slug']],
                [
                    'name_en' => $item['name_en'],
                    'name_hi' => null,
                    'name_gu' => null,
                    'status' => 'active',
                    'sort_order' => $item['sort_order'],
                    'badge_color' => $item['badge_color'],
                ]
            );
        }
    }
}
