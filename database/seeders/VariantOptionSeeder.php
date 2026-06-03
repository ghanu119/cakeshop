<?php

namespace Database\Seeders;

use App\Models\VariantOptionType;
use App\Models\VariantOptionValue;
use Illuminate\Database\Seeder;

class VariantOptionSeeder extends Seeder
{
    public function run(): void
    {
        $weightType = VariantOptionType::firstOrCreate(
            ['slug' => 'weight'],
            [
                'name_en' => 'Weight',
                'selection_mode' => 'single',
                'sort_order' => 1,
                'status' => 'active',
            ]
        );

        $weights = [
            ['label' => '250 gm', 'grams' => 250, 'sort_order' => 1],
            ['label' => '500 gm', 'grams' => 500, 'sort_order' => 2],
            ['label' => '1 KG', 'grams' => 1000, 'sort_order' => 3],
            ['label' => '2 KG', 'grams' => 2000, 'sort_order' => 4],
            ['label' => '3 KG', 'grams' => 3000, 'sort_order' => 5],
        ];

        foreach ($weights as $weight) {
            VariantOptionValue::firstOrCreate(
                [
                    'variant_option_type_id' => $weightType->id,
                    'grams' => $weight['grams'],
                ],
                [
                    'label' => $weight['label'],
                    'sort_order' => $weight['sort_order'],
                    'status' => 'active',
                ]
            );
        }
    }
}
