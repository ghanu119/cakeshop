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
            ['label' => '250 gm', 'grams' => 250, 'sort_order' => 1, 'person_capacity_label' => '2 - 3 People'],
            ['label' => '500 gm', 'grams' => 500, 'sort_order' => 2, 'person_capacity_label' => '4 - 5 People'],
            ['label' => '1 KG', 'grams' => 1000, 'sort_order' => 3, 'person_capacity_label' => '8 - 10 People'],
            ['label' => '2 KG', 'grams' => 2000, 'sort_order' => 4, 'person_capacity_label' => '15 - 20 People'],
            ['label' => '3 KG', 'grams' => 3000, 'sort_order' => 5, 'person_capacity_label' => '25 - 30 People'],
        ];

        foreach ($weights as $weight) {
            $value = VariantOptionValue::firstOrCreate(
                [
                    'variant_option_type_id' => $weightType->id,
                    'grams' => $weight['grams'],
                ],
                [
                    'label' => $weight['label'],
                    'person_capacity_label' => $weight['person_capacity_label'],
                    'sort_order' => $weight['sort_order'],
                    'status' => 'active',
                ]
            );

            $value->update([
                'label' => $weight['label'],
                'person_capacity_label' => $weight['person_capacity_label'],
                'sort_order' => $weight['sort_order'],
                'status' => 'active',
            ]);
        }
    }
}
