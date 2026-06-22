<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $seeders = [
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            ServiceablePincodeSeeder::class,
            VariantOptionSeeder::class,
            FlavorSeeder::class,
            CategorySeeder::class,
            FeatureSeeder::class,
            TestimonialSeeder::class,
        ];

        if (! app()->environment('production')) {
            $seeders = array_merge($seeders, [
                ProductSeeder::class,
            ]);
        }

        $this->call($seeders);
    }
}
