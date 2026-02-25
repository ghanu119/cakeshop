<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seeded users use password: Str0n9@123 (plan requirement).
     */
    private const SEED_PASSWORD = 'Str0n9@123';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make(self::SEED_PASSWORD),
            ]
        );
        if (! $admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }

        $kitchen = User::firstOrCreate(
            ['email' => 'kitchen@example.com'],
            [
                'name' => 'Kitchen',
                'password' => Hash::make(self::SEED_PASSWORD),
            ]
        );
        if (! $kitchen->hasRole('Kitchen')) {
            $kitchen->assignRole('Kitchen');
        }
    }
}
