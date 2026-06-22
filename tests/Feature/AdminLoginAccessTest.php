<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AuthGuards;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    public function test_storefront_customer_login_does_not_authenticate_admin(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $this->actingAsStorefrontCustomer($customer)
            ->get(route('admin.login'))
            ->assertOk()
            ->assertSee(__('Admin Login'), false);

        $this->assertGuest(AuthGuards::STAFF);
        $this->assertAuthenticated(AuthGuards::CUSTOMER);
    }

    public function test_logged_in_staff_is_redirected_from_admin_login_page(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $this->actingAs($admin, AuthGuards::STAFF)
            ->get(route('admin.login'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_and_customer_can_both_be_logged_in(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $admin = User::factory()->create([
            'email' => 'staff@example.com',
            'password' => Hash::make('Str0n9@123'),
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('Admin');

        $this->actingAsStorefrontCustomer($customer)
            ->post(route('admin.login.post'), [
                'email' => 'staff@example.com',
                'password' => 'Str0n9@123',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated(AuthGuards::STAFF);
        $this->assertAuthenticated(AuthGuards::CUSTOMER);
        $this->assertAuthenticatedAs($admin, AuthGuards::STAFF);
        $this->assertAuthenticatedAs($customer, AuthGuards::CUSTOMER);
    }

    public function test_customer_can_access_account_while_staff_is_also_logged_in(): void
    {
        $customer = User::factory()->create();
        $customer->assignRole('Customer');

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $this->actingAsStorefrontCustomer($customer)
            ->actingAs($admin, AuthGuards::STAFF)
            ->get(route('account.dashboard'))
            ->assertOk();

        $this->assertAuthenticated(AuthGuards::CUSTOMER);
        $this->assertAuthenticated(AuthGuards::STAFF);
    }

    public function test_staff_without_customer_session_is_redirected_from_account(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->assignRole('Admin');

        $this->actingAs($admin, AuthGuards::STAFF)
            ->get(route('account.dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }
}
