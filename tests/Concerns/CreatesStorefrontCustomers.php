<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Support\AuthGuards;
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

    protected function actingAsStorefrontCustomer(User $user): static
    {
        return $this->actingAs($user, AuthGuards::CUSTOMER);
    }
}
