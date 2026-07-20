<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsersListTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleAndPermissionSeeder::class);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        return $admin;
    }

    public function test_users_index_excludes_customers(): void
    {
        $admin = $this->adminUser();

        $kitchen = User::factory()->create(['name' => 'Kitchen Staff', 'email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        $customer = User::factory()->customer()->create(['name' => 'Store Customer']);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Kitchen Staff');
        $response->assertDontSee('Store Customer');
    }

    public function test_users_index_includes_staff_roles(): void
    {
        $admin = $this->adminUser();

        $kitchen = User::factory()->create(['name' => 'Kitchen User', 'email_verified_at' => now()]);
        $kitchen->assignRole('Kitchen');

        $otherAdmin = User::factory()->create(['name' => 'Other Admin', 'email_verified_at' => now()]);
        $otherAdmin->assignRole('Admin');

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Kitchen User');
        $response->assertSee('Other Admin');
    }

    public function test_users_role_filter_does_not_include_customer(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertDontSee('value="Customer"', false);
    }

    public function test_cannot_edit_customer_via_users_route(): void
    {
        $admin = $this->adminUser();
        $customer = User::factory()->customer()->create();

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $customer));

        $response->assertForbidden();
    }

    public function test_cannot_assign_customer_role_when_creating_user(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Bad Role User',
            'email' => 'badrole@example.com',
            'password' => 'Str0n9@123',
            'password_confirmation' => 'Str0n9@123',
            'roles' => ['Customer'],
        ]);

        $response->assertSessionHasErrors('roles.0');
        $this->assertDatabaseMissing('users', [
            'email' => 'badrole@example.com',
        ]);
    }
}
