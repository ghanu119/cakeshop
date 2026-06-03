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
        $this->call([
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            SettingSeeder::class,
            VariantOptionSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            FeatureSeeder::class,
            TestimonialSeeder::class,
        ]);
    }
}
