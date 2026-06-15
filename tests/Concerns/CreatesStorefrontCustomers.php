<?php

namespace Tests\Concerns;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;

trait CreatesStorefrontCustomers
{
    protected function seedRolesIfNeeded(): void
    {
        if (! \Spatie\Permission\Models\Role::where('name', 'Customer')->exists()) {
            $this->seed(RoleAndPermissionSeeder::class);
        }
    }

    protected function createStorefrontCustomer(array $attributes = []): User
    {
        $this->seedRolesIfNeeded();

        return User::factory()->customer()->create($attributes);
    }
}
