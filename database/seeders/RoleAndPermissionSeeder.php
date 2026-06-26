<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $guard = config('auth.defaults.guard');

        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions (plan: categories, products, orders, users, settings, contact_enquiries, features, testimonials)
        $permissions = [
            'categories.view',
            'categories.create',
            'categories.update',
            'categories.delete',
            'products.view',
            'products.create',
            'products.update',
            'products.delete',
            'orders.view',
            'orders.update',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'customers.view',
            'customers.create',
            'customers.update',
            'customers.delete',
            'customers.impersonate',
            'settings.manage',
            'contact_enquiries.view',
            'features.view',
            'features.create',
            'features.update',
            'features.delete',
            'testimonials.view',
            'testimonials.create',
            'testimonials.update',
            'testimonials.delete',
            'home_sliders.view',
            'home_sliders.create',
            'home_sliders.update',
            'home_sliders.delete',
            'sliders.view',
            'sliders.update',
            'slider_items.view',
            'slider_items.create',
            'slider_items.update',
            'slider_items.delete',
            'flavors.view',
            'flavors.create',
            'flavors.update',
            'flavors.delete',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => $guard]);
        }

        // Admin: all permissions
        $admin = Role::firstOrCreate(['name' => 'Admin', 'guard_name' => $guard]);
        $admin->givePermissionTo(Permission::all());

        // Kitchen: orders view and update only
        $kitchen = Role::firstOrCreate(['name' => 'Kitchen', 'guard_name' => $guard]);
        $kitchen->syncPermissions(['orders.view', 'orders.update']);

        // Customer: no admin permissions (storefront only)
        Role::firstOrCreate(['name' => 'Customer', 'guard_name' => $guard]);

        // Re-give Admin all permissions (in case new ones were added)
        $admin->syncPermissions(Permission::all());
    }
}
